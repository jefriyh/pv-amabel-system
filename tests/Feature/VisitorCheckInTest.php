<?php

namespace Tests\Feature;

use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisitorCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Telegram dikirim langsung saat request, jadi yang di-fake adalah panggilan
        // HTTP-nya, bukan antrean.
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        config()->set('telegram.enabled', true);
    }

    public function test_form_tamu_bisa_dibuka(): void
    {
        $this->get(route('visitors.create'))
            ->assertOk()
            ->assertSee('Foto KTP')
            ->assertSee('Foto selfie');
    }

    public function test_tamu_tersimpan_beserta_fotonya(): void
    {
        $response = $this->post(route('visitors.store'), [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'host_name' => 'Pak Andi, Blok C2',
            'purpose' => 'Silaturahmi keluarga',
            'ktp' => UploadedFile::fake()->image('ktp.jpg', 2400, 1600),
            'selfie' => UploadedFile::fake()->image('selfie.jpg', 1200, 1600),
        ]);

        $response->assertRedirect(route('guestbook.done'));

        $visitor = Visitor::sole();

        $this->assertSame('Budi Santoso', $visitor->name);
        $this->assertSame('081234567890', $visitor->phone);

        // Foto harus mendarat di disk privat, di dalam folder milik entri ini.
        $this->assertStringStartsWith("visitors/{$visitor->id}/", $visitor->ktp_path);
        Storage::disk('local')->assertExists($visitor->ktp_path);
        Storage::disk('local')->assertExists($visitor->selfie_path);

        // Semuanya diseragamkan menjadi JPEG, apa pun format aslinya.
        $this->assertStringEndsWith('.jpg', $visitor->ktp_path);
    }

    public function test_kartu_bukti_memuat_identitas_dan_instruksi(): void
    {
        // Sengaja mengikuti redirect: kalau hanya assertRedirect, halaman tujuannya
        // tidak pernah benar-benar dirender sehingga error Blade di sana lolos.
        $this->followingRedirects()
            ->post(route('visitors.store'), $this->validPayload())
            ->assertOk()
            ->assertSee('Tunjukkan ini ke security', false)
            ->assertSee('dihampiri pemilik rumah', false)
            ->assertSee('Tamu Terdaftar')
            ->assertSee('Budi Santoso')
            ->assertSee('Pak Andi, Blok C2')
            ->assertSee('Silaturahmi keluarga')
            // Kedua foto tampil sebagai lampiran kartu.
            ->assertSee(route('guestbook.receipt-photo', ['field' => 'ktp_path']), false)
            ->assertSee(route('guestbook.receipt-photo', ['field' => 'selfie_path']), false);
    }

    public function test_foto_pada_kartu_bukti_bisa_dibuka_pemiliknya(): void
    {
        $this->post(route('visitors.store'), $this->validPayload());

        foreach (['ktp_path', 'selfie_path'] as $field) {
            $this->get(route('guestbook.receipt-photo', ['field' => $field]))
                ->assertOk()
                ->assertHeader('Content-Type', 'image/jpeg');
        }
    }

    public function test_foto_kartu_bukti_tidak_bisa_dibuka_pengunjung_lain(): void
    {
        $this->post(route('visitors.store'), $this->validPayload());

        // Session baru = browser lain. Tanpa session, tidak ada yang bisa dilihat,
        // dan id entri tidak pernah muncul di URL sehingga tidak bisa ditebak.
        $this->flushSession();

        $this->get(route('guestbook.receipt-photo', ['field' => 'ktp_path']))
            ->assertNotFound();
    }

    public function test_kolom_selain_foto_ditolak_route_kartu_bukti(): void
    {
        $this->post(route('visitors.store'), $this->validPayload());

        $this->get(route('guestbook.receipt-photo', ['field' => 'ip_address']))
            ->assertNotFound();
    }

    public function test_halaman_selesai_tanpa_session_mengarahkan_isi_formulir(): void
    {
        // Tamu yang membuka /selesai langsung, mis. dari riwayat browser.
        $this->get(route('guestbook.done'))
            ->assertOk()
            ->assertSee('Belum ada data')
            ->assertDontSee('Tunjukkan ini ke security', false);
    }

    public function test_notifikasi_telegram_terkirim_saat_submit(): void
    {
        $this->post(route('visitors.store'), $this->validPayload());

        Http::assertSent(function ($request) {
            $this->assertStringContainsString('/sendMessage', $request->url());

            // Isi pesan tidak boleh memuat foto — hanya ringkasan dan tautan dashboard.
            $this->assertStringContainsString('TAMU MASUK', $request['text']);
            $this->assertStringContainsString('Budi Santoso', $request['text']);
            $this->assertStringContainsString('/admin/visitors/', $request['text']);

            return true;
        });
    }

    public function test_submit_tetap_sukses_walau_telegram_bermasalah(): void
    {
        // Data tamu sudah tercatat dengan selamat; gangguan Telegram tidak boleh
        // membuat tamu di gerbang melihat halaman error.
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'chat not found'], 400)]);

        $this->followingRedirects()
            ->post(route('visitors.store'), $this->validPayload())
            ->assertOk()
            ->assertSee('Tamu Terdaftar')
            ->assertSee('Budi Santoso');

        $this->assertSame(1, Visitor::count());
    }

    public function test_tidak_menghubungi_telegram_kalau_belum_dikonfigurasi(): void
    {
        config()->set('telegram.enabled', false);

        $this->post(route('visitors.store'), $this->validPayload())
            ->assertRedirect(route('guestbook.done'));

        Http::assertNothingSent();
        $this->assertSame(1, Visitor::count());
    }

    public function test_foto_diperkecil_ke_batas_maksimum(): void
    {
        $this->post(route('visitors.store'), [
            ...$this->validPayload(),
            'ktp' => UploadedFile::fake()->image('ktp.jpg', 4000, 3000),
        ]);

        $visitor = Visitor::sole();
        $contents = Storage::disk('local')->get($visitor->ktp_path);

        [$width, $height] = getimagesizefromstring($contents);

        $this->assertSame(1600, $width);
        $this->assertSame(1200, $height);
    }

    public function test_semua_field_wajib_divalidasi(): void
    {
        $this->post(route('visitors.store'), [])
            ->assertSessionHasErrors(['name', 'phone', 'host_name', 'purpose', 'ktp', 'selfie']);

        $this->assertSame(0, Visitor::count());
    }

    public function test_nomor_hp_dan_tujuan_wajib_diisi(): void
    {
        // Keduanya dipakai pengurus untuk menghubungi tamu dan memastikan
        // kedatangannya memang ditunggu, jadi tidak boleh kosong.
        $this->post(route('visitors.store'), [
            ...$this->validPayload(),
            'phone' => '',
            'host_name' => '',
        ])->assertSessionHasErrors(['phone', 'host_name']);

        $this->assertSame(0, Visitor::count());
    }

    public function test_nomor_hp_harus_berupa_angka(): void
    {
        $this->post(route('visitors.store'), [
            ...$this->validPayload(),
            'phone' => 'nanti saja',
        ])->assertSessionHasErrors('phone');
    }

    public function test_file_bukan_gambar_ditolak(): void
    {
        $this->post(route('visitors.store'), [
            ...$this->validPayload(),
            'ktp' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('ktp');

        $this->assertSame(0, Visitor::count());
    }

    public function test_honeypot_menolak_bot(): void
    {
        $this->post(route('visitors.store'), [
            ...$this->validPayload(),
            'website' => 'https://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, Visitor::count());
        Http::assertNothingSent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'host_name' => 'Pak Andi, Blok C2',
            'purpose' => 'Silaturahmi keluarga',
            'ktp' => UploadedFile::fake()->image('ktp.jpg', 1200, 800),
            'selfie' => UploadedFile::fake()->image('selfie.jpg', 800, 1000),
        ];
    }
}
