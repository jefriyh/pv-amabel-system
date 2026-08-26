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
        $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_admin_bisa_membuka_daftar_dan_detail(): void
    {
        $visitor = $this->makeVisitor();
        $package = $this->makePackage();

        $this->actingAs($this->admin());

        $this->get('/admin')->assertOk();
        $this->get('/admin/visitors')->assertOk()->assertSee($visitor->name);
        $this->get("/admin/visitors/{$visitor->id}")->assertOk()->assertSee('Silaturahmi keluarga');
        $this->get('/admin/package-deliveries')->assertOk()->assertSee($package->courier_name);
        $this->get("/admin/package-deliveries/{$package->id}")->assertOk();
    }

    public function test_admin_tidak_bisa_membuat_atau_menyunting_entri(): void
    {
        $visitor = $this->makeVisitor();

        $this->actingAs($this->admin());

        // Halaman create/edit sengaja tidak didaftarkan: buku tamu hanya boleh diisi
        // dari form di gerbang supaya tetap sah sebagai catatan.
        $this->get('/admin/visitors/create')->assertNotFound();
        $this->get("/admin/visitors/{$visitor->id}/edit")->assertNotFound();
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

        $this->actingAs($this->admin())
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

        $this->actingAs($this->admin())
            ->get(route('admin.media', [
                'type' => 'visitors',
                'record' => $visitor->id,
                'field' => 'ip_address',
            ]))
            ->assertNotFound();
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Komplek',
            'email' => 'admin@example.test',
            'password' => 'rahasia123',
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
        ]);

        $package->id = $id;
        $package->save();

        return $package;
    }
}
