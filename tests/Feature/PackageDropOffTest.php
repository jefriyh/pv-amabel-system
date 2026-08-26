<?php

namespace Tests\Feature;

use App\Models\PackageDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageDropOffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        config()->set('telegram.enabled', true);
    }

    public function test_form_paket_menampilkan_daftar_ekspedisi(): void
    {
        $this->get(route('packages.create'))
            ->assertOk()
            ->assertSee('JNE')
            ->assertSee('SiCepat');
    }

    public function test_paket_tersimpan_tanpa_selfie(): void
    {
        $this->post(route('packages.store'), [
            'courier_name' => 'Rizal Pratama',
            'courier_company' => 'J&T Express',
            'recipient_note' => 'Ibu Sari, Blok B1 No. 12',
            'tracking_number' => 'JT1234567890',
            'photo' => UploadedFile::fake()->image('paket.jpg'),
        ])->assertRedirect(route('guestbook.done'));

        $package = PackageDelivery::sole();

        $this->assertSame('Rizal Pratama', $package->courier_name);
        $this->assertNull($package->selfie_path);
        Storage::disk('local')->assertExists($package->photo_path);

        Http::assertSent(function ($request) {
            $this->assertStringContainsString('PAKET MASUK KE KOTAK', $request['text']);
            $this->assertStringContainsString('JT1234567890', $request['text']);

            return true;
        });
    }

    public function test_submit_tetap_sukses_walau_telegram_bermasalah(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response('gateway timeout', 504)]);

        $this->followingRedirects()
            ->post(route('packages.store'), $this->validPayload())
            ->assertOk()
            ->assertSee('Paket Tercatat')
            ->assertSee('Rizal Pratama');

        $this->assertSame(1, PackageDelivery::count());
    }

    public function test_kartu_bukti_paket_memuat_data_pengiriman(): void
    {
        $this->followingRedirects()
            ->post(route('packages.store'), [
                ...$this->validPayload(),
                'recipient_note' => 'Ibu Sari, Blok B1 No. 12',
                'tracking_number' => 'JT1234567890',
            ])
            ->assertOk()
            ->assertSee('Paket Tercatat')
            ->assertSee('Tunjukkan ini ke security', false)
            ->assertSee('Rizal Pratama')
            ->assertSee('Ibu Sari, Blok B1 No. 12')
            ->assertSee('JT1234567890');
    }

    public function test_kartu_bukti_paket_tidak_menampilkan_selfie_yang_tidak_diisi(): void
    {
        // Selfie kurir opsional. Kalau tidak diisi, kartunya tidak boleh memuat
        // gambar rusak.
        $this->followingRedirects()
            ->post(route('packages.store'), $this->validPayload())
            ->assertOk()
            ->assertSee(route('guestbook.receipt-photo', ['field' => 'photo_path']), false)
            ->assertDontSee(route('guestbook.receipt-photo', ['field' => 'selfie_path']), false);
    }

    public function test_selfie_kurir_ikut_tersimpan_kalau_diisi(): void
    {
        $this->post(route('packages.store'), [
            ...$this->validPayload(),
            'selfie' => UploadedFile::fake()->image('kurir.jpg'),
        ]);

        $package = PackageDelivery::sole();

        $this->assertNotNull($package->selfie_path);
        Storage::disk('local')->assertExists($package->selfie_path);
    }

    public function test_foto_paket_wajib_ada_sebagai_bukti(): void
    {
        $this->post(route('packages.store'), [
            ...$this->validPayload(),
            'photo' => null,
        ])->assertSessionHasErrors('photo');

        $this->assertSame(0, PackageDelivery::count());
    }

    public function test_ekspedisi_di_luar_daftar_ditolak(): void
    {
        $this->post(route('packages.store'), [
            ...$this->validPayload(),
            'courier_company' => 'Ekspedisi Karangan',
        ])->assertSessionHasErrors('courier_company');
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'courier_name' => 'Rizal Pratama',
            'courier_company' => 'JNE',
            'photo' => UploadedFile::fake()->image('paket.jpg'),
        ];
    }
}
