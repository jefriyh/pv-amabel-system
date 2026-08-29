<?php

namespace Tests\Feature;

use App\Models\Visitor;
use App\Services\TelegramNotifier;
use App\Support\TelegramMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('telegram.enabled', true);
        config()->set('telegram.token', '123456:test-token');
        config()->set('telegram.chat_id', '-1001234567890');
    }

    public function test_pesan_terkirim_ke_endpoint_dan_grup_yang_benar(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        app(TelegramNotifier::class)->send('halo');

        Http::assertSent(function ($request) {
            $this->assertStringContainsString('/bot123456:test-token/sendMessage', $request->url());
            $this->assertSame('-1001234567890', $request['chat_id']);
            $this->assertSame('HTML', $request['parse_mode']);

            return true;
        });
    }

    public function test_thread_id_dikirim_hanya_kalau_diisi(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        app(TelegramNotifier::class)->send('halo');
        Http::assertSent(fn ($request) => ! isset($request['message_thread_id']));

        config()->set('telegram.thread_id', '42');
        app(TelegramNotifier::class)->send('halo');
        Http::assertSent(fn ($request) => ($request['message_thread_id'] ?? null) === 42);
    }

    public function test_penolakan_telegram_dilempar_oleh_send(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'chat not found'], 400)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('chat not found');

        // send() melempar supaya command telegram:test bisa menampilkan penyebabnya.
        app(TelegramNotifier::class)->send('halo');
    }

    public function test_send_quietly_menelan_kegagalan_dan_melaporkannya(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'chat not found'], 400)]);
        Log::spy();

        $terkirim = app(TelegramNotifier::class)->sendQuietly('halo');

        $this->assertFalse($terkirim);
        Log::shouldHaveReceived('error')
            ->withArgs(fn (string $message) => str_contains($message, 'chat not found'));
    }

    public function test_send_quietly_melaporkan_sukses(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $this->assertTrue(app(TelegramNotifier::class)->sendQuietly('halo'));
    }

    public function test_send_quietly_mengembalikan_false_kalau_belum_dikonfigurasi(): void
    {
        Http::fake();
        config()->set('telegram.enabled', false);

        $this->assertFalse(app(TelegramNotifier::class)->sendQuietly('halo'));
        Http::assertNothingSent();
    }

    public function test_tidak_mengirim_apa_pun_kalau_belum_dikonfigurasi(): void
    {
        Http::fake();
        config()->set('telegram.enabled', false);

        app(TelegramNotifier::class)->send('halo');

        Http::assertNothingSent();
    }

    public function test_gangguan_sesaat_dicoba_ulang_sekali(): void
    {
        // Percobaan pertama gagal, percobaan kedua berhasil: tamu tetap dapat
        // notifikasi tanpa perlu antrean.
        Http::fake(['api.telegram.org/*' => Http::sequence()
            ->push('service unavailable', 503)
            ->push(['ok' => true], 200),
        ]);

        app(TelegramNotifier::class)->send('isi pesan');

        Http::assertSentCount(2);
    }

    public function test_karakter_html_dari_input_tamu_di_escape(): void
    {
        // Kalau tidak di-escape, satu tanda "<" dari nama tamu membuat Telegram
        // menolak seluruh pesan dan notifikasi hilang tanpa jejak.
        $visitor = $this->makeVisitor('Budi <script>alert(1)</script>');

        $message = TelegramMessage::forVisitor($visitor, 'https://guestbook.test/admin/visitors/1');

        $this->assertStringNotContainsString('<script>', $message);
        $this->assertStringContainsString('&lt;script&gt;', $message);
    }

    public function test_pesan_tidak_pernah_memuat_foto(): void
    {
        $visitor = $this->makeVisitor('Budi Santoso');

        $message = TelegramMessage::forVisitor($visitor, 'https://guestbook.test/admin/visitors/1');

        $this->assertStringNotContainsString($visitor->ktp_path, $message);
        $this->assertStringContainsString('https://guestbook.test/admin/visitors/1', $message);
        $this->assertStringContainsString('Buka Detail di Dashboard', $message);
    }

    public function test_pesan_notifikasi_presensi_security_terformat_dengan_baik(): void
    {
        $user = \App\Models\User::create([
            'name' => 'Bambang Security',
            'email' => 'bambang@amabel.test',
            'password' => 'secret123',
            'role' => \App\Models\User::ROLE_SECURITY,
            'is_active' => true,
        ]);

        $prevUser = \App\Models\User::create([
            'name' => 'Slamet Security',
            'email' => 'slamet@amabel.test',
            'password' => 'secret123',
            'role' => \App\Models\User::ROLE_SECURITY,
            'is_active' => true,
        ]);

        $attendance = \App\Models\SecurityAttendance::create([
            'user_id' => $user->id,
            'previous_security_id' => $prevUser->id,
            'type' => \App\Models\SecurityAttendance::TYPE_MASUK,
            'attendance_date' => now()->toDateString(),
            'day_name' => 'Sabtu',
            'attendance_time' => '08:00:00',
            'start_time' => '08:00',
            'end_time' => '20:00',
            'location_address' => 'Pos Gerbang Utama Villa Amabel',
            'latitude' => -6.442989,
            'longitude' => 106.823221,
            'selfie_path' => 'attendances/selfie.jpg',
            'status' => 'hadir',
            'notes' => 'Serah terima HT dan kunci pos aman',
        ]);

        $attendance->load(['user', 'previousSecurity']);
        $message = TelegramMessage::forAttendance($attendance, 'https://amabel.test/internal/security-attendances/' . $attendance->id);

        $this->assertStringContainsString('LOG PRESENSI KEHADIRAN SECURITY', $message);
        $this->assertStringContainsString('Bambang Security', $message);
        $this->assertStringContainsString('Slamet Security', $message);
        $this->assertStringContainsString('Pos Gerbang Utama Villa Amabel', $message);
        $this->assertStringContainsString('https://www.google.com/maps?q=-6.442989,106.823221', $message);
        $this->assertStringContainsString('https://amabel.test/internal/security-attendances/' . $attendance->id, $message);
        $this->assertStringContainsString('Buka Detail di Dashboard', $message);
        $this->assertStringNotContainsString('attendances/selfie.jpg', $message);
    }

    public function test_pesan_notifikasi_pengajuan_cuti_izin_sakit_terformat_dengan_baik(): void
    {
        $user = \App\Models\User::create([
            'name' => 'Bambang Security',
            'email' => 'bambang.cuti@amabel.test',
            'password' => 'secret123',
            'role' => \App\Models\User::ROLE_SECURITY,
            'annual_leave_quota' => 12,
            'is_active' => true,
        ]);

        $leave = \App\Models\LeaveRequest::create([
            'user_id' => $user->id,
            'type' => \App\Models\LeaveRequest::TYPE_CUTI,
            'selected_dates' => [now()->addDays(3)->toDateString(), now()->addDays(4)->toDateString()],
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'total_days' => 2,
            'reason' => 'Acara keluarga di kampung',
            'status' => \App\Models\LeaveRequest::STATUS_PENDING,
        ]);

        $leave->load('user');
        $message = TelegramMessage::forLeaveRequest($leave, 'https://amabel.test/internal/leave-requests/' . $leave->id);

        $this->assertStringContainsString('PENGAJUAN CUTI SECURITY', $message);
        $this->assertStringContainsString('Bambang Security', $message);
        $this->assertStringContainsString('2 Hari', $message);
        $this->assertStringContainsString('Acara keluarga di kampung', $message);
        $this->assertStringContainsString('Menunggu Persetujuan Pengurus', $message);
        $this->assertStringContainsString('https://amabel.test/internal/leave-requests/' . $leave->id, $message);
    }

    private function makeVisitor(string $name): Visitor
    {
        $visitor = new Visitor([
            'name' => $name,
            'purpose' => 'Silaturahmi',
            'ktp_path' => 'visitors/abc/ktp.jpg',
            'selfie_path' => 'visitors/abc/selfie.jpg',
        ]);

        $visitor->id = (string) Str::ulid();
        $visitor->save();

        return $visitor;
    }
}
