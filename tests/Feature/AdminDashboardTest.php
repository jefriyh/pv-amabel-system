<?php

namespace Tests\Feature;

use App\Models\PackageDelivery;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_dashboard_butuh_login(): void
    {
        $this->get('/internal')->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_superadmin_bisa_membuka_semua_modul(): void
    {
        $visitor = $this->makeVisitor();
        $package = $this->makePackage();

        $this->actingAs($this->superAdmin());

        $this->get('/internal')->assertOk();
        $this->get('/internal/visitors')->assertOk()->assertSee($visitor->name);
        $this->get("/internal/visitors/{$visitor->id}")->assertOk()->assertSee('Silaturahmi keluarga');
        $this->get('/internal/package-deliveries')->assertOk()->assertSee($package->courier_name);
        $this->get("/internal/package-deliveries/{$package->id}")->assertOk();
        $this->get('/internal/users')->assertOk();
        $this->get('/internal/security-attendances')->assertOk();
        $this->get('/internal/leave-requests')->assertOk();
    }

    public function test_foto_tidak_bisa_diambil_tanpa_login(): void
    {
        $visitor = $this->makeVisitor();

        $this->get(route('admin.media', [
            'type' => 'visitors',
            'record' => $visitor->id,
            'field' => 'ktp_path',
        ]))->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_admin_bisa_membuka_foto(): void
    {
        $visitor = $this->makeVisitor();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.media', [
                'type' => 'visitors',
                'record' => $visitor->id,
                'field' => 'ktp_path',
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_kolom_selain_foto_tidak_bisa_dibaca_lewat_route_media(): void
    {
        $visitor = $this->makeVisitor();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.media', [
                'type' => 'visitors',
                'record' => $visitor->id,
                'field' => 'ip_address',
            ]))
            ->assertNotFound();
    }

    private function superAdmin(): User
    {
        return User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@amabel.id',
            'password' => 'rahasia123',
            'role' => User::ROLE_SUPERADMIN,
            'annual_leave_quota' => 12,
            'is_active' => true,
        ]);
    }

    private function makeVisitor(): Visitor
    {
        $id = (string) Str::ulid();

        Storage::disk('local')->put("visitors/{$id}/ktp.jpg", 'gambar-palsu');
        Storage::disk('local')->put("visitors/{$id}/selfie.jpg", 'gambar-palsu');

        $visitor = new Visitor([
            'name' => 'Budi Santoso',
            'purpose' => 'Silaturahmi keluarga',
            'status' => Visitor::STATUS_PENDING,
            'ktp_path' => "visitors/{$id}/ktp.jpg",
            'selfie_path' => "visitors/{$id}/selfie.jpg",
        ]);

        $visitor->id = $id;
        $visitor->save();

        return $visitor;
    }

    private function makePackage(): PackageDelivery
    {
        $id = (string) Str::ulid();

        Storage::disk('local')->put("packages/{$id}/paket.jpg", 'gambar-palsu');

        $package = new PackageDelivery([
            'courier_name' => 'Rizal Pratama',
            'courier_company' => 'JNE',
            'photo_path' => "packages/{$id}/paket.jpg",
            'status' => PackageDelivery::STATUS_DITITIPKAN,
        ]);

        $package->id = $id;
        $package->save();

        return $package;
    }
}
