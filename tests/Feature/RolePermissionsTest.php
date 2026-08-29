<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\LeaveRequest;
use App\Models\PackageDelivery;
use App\Models\SecurityAttendance;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_pengurus_dan_security_dapat_login_menggunakan_email(): void
    {
        $user = User::create([
            'name' => 'Pengurus RT',
            'email' => 'pengurus@amabel.id',
            'phone' => '081234567891',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_PENGURUS,
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'login' => 'pengurus@amabel.id',
                'password' => 'password123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_pengurus_dan_security_dapat_login_menggunakan_nomor_hp(): void
    {
        $security = User::create([
            'name' => 'Security Budi',
            'email' => null,
            'phone' => '081234567899',
            'password' => Hash::make('securitypass'),
            'role' => User::ROLE_SECURITY,
            'annual_leave_quota' => 12,
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'login' => '081234567899',
                'password' => 'securitypass',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($security);
    }

    public function test_superadmin_dapat_mengakses_manajemen_pengguna(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@amabel.id',
            'password' => 'secret123',
            'role' => User::ROLE_SUPERADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($superadmin)
            ->get('/internal/users')
            ->assertOk();
    }

    public function test_pengurus_dan_security_tidak_dapat_mengakses_manajemen_pengguna(): void
    {
        $pengurus = User::create([
            'name' => 'Pengurus RT',
            'email' => 'pengurus@amabel.id',
            'password' => 'secret123',
            'role' => User::ROLE_PENGURUS,
            'is_active' => true,
        ]);

        $security = User::create([
            'name' => 'Security Budi',
            'email' => 'security@amabel.id',
            'password' => 'secret123',
            'role' => User::ROLE_SECURITY,
            'is_active' => true,
        ]);

        $this->actingAs($pengurus)
            ->get('/internal/users')
            ->assertForbidden();

        $this->actingAs($security)
            ->get('/internal/users')
            ->assertForbidden();
    }

    public function test_security_dapat_mencatat_presensi_dan_mengajukan_cuti(): void
    {
        $security = User::create([
            'name' => 'Security Budi',
            'email' => 'security@amabel.id',
            'password' => 'secret123',
            'role' => User::ROLE_SECURITY,
            'annual_leave_quota' => 12,
            'is_active' => true,
        ]);

        $attendance = SecurityAttendance::create([
            'user_id' => $security->id,
            'type' => SecurityAttendance::TYPE_MASUK,
            'attendance_date' => now()->toDateString(),
            'day_name' => 'Sabtu',
            'attendance_time' => '08:00:00',
            'selfie_path' => 'attendances/sample.jpg',
            'status' => 'hadir',
        ]);

        $leave = LeaveRequest::create([
            'user_id' => $security->id,
            'type' => LeaveRequest::TYPE_CUTI,
            'selected_dates' => [now()->addDays(5)->toDateString(), now()->addDays(6)->toDateString(), now()->addDays(7)->toDateString()],
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'total_days' => 3,
            'reason' => 'Keperluan keluarga di kampung',
            'status' => LeaveRequest::STATUS_PENDING,
        ]);

        $this->actingAs($security);

        // Security bisa melihat halaman attendance & leave
        $this->get('/internal/security-attendances')->assertOk()->assertSee('Security Budi');
        $this->get('/internal/leave-requests')->assertOk()->assertSee('Keperluan keluarga');

        // Check remaining quota calculation
        $this->assertSame(12, $security->remaining_leave_quota);

        // Pengurus menyetujui cuti
        $pengurus = User::create([
            'name' => 'Pengurus RT',
            'email' => 'pengurus@amabel.id',
            'password' => 'secret123',
            'role' => User::ROLE_PENGURUS,
            'is_active' => true,
        ]);

        $leave->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $pengurus->id,
            'approved_at' => now(),
        ]);

        $security->refresh();
        $this->assertSame(9, $security->remaining_leave_quota);
    }
}
