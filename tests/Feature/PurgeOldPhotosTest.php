<?php

namespace Tests\Feature;

use App\Models\PackageDelivery;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurgeOldPhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_foto_lama_dihapus_tapi_rekapnya_tetap_ada(): void
    {
        $lama = $this->makeVisitor(daysAgo: 120);
        $baru = $this->makeVisitor(daysAgo: 3);

        $this->artisan('guestbook:purge-photos')->assertSuccessful();

        Storage::disk('local')->assertMissing("visitors/{$lama->id}/ktp.jpg");
        Storage::disk('local')->assertExists("visitors/{$baru->id}/ktp.jpg");

        $lama->refresh();

        // Barisnya harus tetap ada sebagai rekap kunjungan, hanya fotonya yang hilang.
        $this->assertNotNull($lama->fresh());
        $this->assertNull($lama->ktp_path);
        $this->assertNull($lama->selfie_path);
        $this->assertNotNull($lama->photos_purged_at);

        $this->assertNotNull($baru->fresh()->ktp_path);
    }

    public function test_dry_run_tidak_menghapus_apa_pun(): void
    {
        $lama = $this->makeVisitor(daysAgo: 120);

        $this->artisan('guestbook:purge-photos --dry-run')->assertSuccessful();

        Storage::disk('local')->assertExists("visitors/{$lama->id}/ktp.jpg");
        $this->assertNotNull($lama->fresh()->ktp_path);
    }

    public function test_batas_hari_bisa_ditimpa_lewat_opsi(): void
    {
        $entry = $this->makeVisitor(daysAgo: 10);

        $this->artisan('guestbook:purge-photos --days=7')->assertSuccessful();

        $this->assertNull($entry->fresh()->ktp_path);
    }

    public function test_paket_ikut_dibersihkan(): void
    {
        $id = (string) Str::ulid();
        Storage::disk('local')->put("packages/{$id}/paket.jpg", 'x');

        $package = new PackageDelivery([
            'courier_name' => 'Rizal',
            'courier_company' => 'JNE',
            'photo_path' => "packages/{$id}/paket.jpg",
        ]);
        $package->id = $id;
        $package->save();
        $package->forceFill(['created_at' => now()->subDays(200)])->saveQuietly();

        $this->artisan('guestbook:purge-photos')->assertSuccessful();

        Storage::disk('local')->assertMissing("packages/{$id}/paket.jpg");
        $this->assertNull($package->fresh()->photo_path);
    }

    private function makeVisitor(int $daysAgo): Visitor
    {
        $id = (string) Str::ulid();

        Storage::disk('local')->put("visitors/{$id}/ktp.jpg", 'x');
        Storage::disk('local')->put("visitors/{$id}/selfie.jpg", 'x');

        $visitor = new Visitor([
            'name' => 'Budi',
            'purpose' => 'Silaturahmi',
            'ktp_path' => "visitors/{$id}/ktp.jpg",
            'selfie_path' => "visitors/{$id}/selfie.jpg",
        ]);

        $visitor->id = $id;
        $visitor->save();
        $visitor->forceFill(['created_at' => now()->subDays($daysAgo)])->saveQuietly();

        return $visitor;
    }
}
