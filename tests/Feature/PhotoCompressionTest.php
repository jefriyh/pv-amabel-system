<?php

namespace Tests\Feature;

use App\Models\PackageDelivery;
use App\Models\Visitor;
use App\Services\PhotoStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menjamin tidak ada berkas unggahan yang mendarat di penyimpanan tanpa dikompres,
 * lewat jalur mana pun: form tamu, form paket, maupun pemanggilan langsung.
 */
class PhotoCompressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    }

    public function test_foto_besar_menyusut_drastis(): void
    {
        $asli = $this->fotoBerukuran(3000, 2000);

        $path = $this->simpan($asli);

        $tersimpan = Storage::disk('local')->size($path);

        $this->assertLessThan(
            $asli->getSize(),
            $tersimpan,
            'Foto hasil simpan seharusnya lebih kecil daripada berkas aslinya.',
        );

        // Sisi terpanjang dibatasi max_dimension.
        [$w, $h] = getimagesizefromstring(Storage::disk('local')->get($path));
        $this->assertSame(config('guestbook.photo.max_dimension'), max($w, $h));
    }

    public function test_png_diubah_menjadi_jpeg(): void
    {
        // PNG foto berukuran besar boros sekali; menyimpannya apa adanya membuat
        // storage cepat penuh.
        $path = $this->simpan(UploadedFile::fake()->image('ktp.png', 2000, 1400));

        $this->assertStringEndsWith('.jpg', $path);

        $info = getimagesizefromstring(Storage::disk('local')->get($path));
        $this->assertSame('image/jpeg', $info['mime']);
    }

    public function test_foto_kecil_pun_tetap_di_encode_ulang(): void
    {
        // Foto di bawah batas ukuran tidak diperkecil, tapi tetap harus di-encode ulang
        // supaya metadata EXIF-nya ikut terbuang.
        $path = $this->simpan(UploadedFile::fake()->image('kecil.png', 400, 300));

        $isi = Storage::disk('local')->get($path);
        $info = getimagesizefromstring($isi);

        $this->assertSame('image/jpeg', $info['mime']);
        $this->assertSame([400, 300], [$info[0], $info[1]]);
    }

    public function test_koordinat_gps_dari_kamera_hp_ikut_terbuang(): void
    {
        $asli = $this->fotoBerGps();

        // Prasyarat: berkas ujinya memang membawa GPS, supaya test ini benar-benar
        // membuktikan sesuatu dan bukan lolos karena kebetulan.
        $this->assertNotFalse(@exif_read_data($asli->getRealPath()));
        $this->assertArrayHasKey('GPSLatitude', @exif_read_data($asli->getRealPath()));

        $isi = Storage::disk('local')->get($this->simpan($asli));

        // Penanda blok APP1/EXIF pada berkas JPEG.
        $this->assertStringNotContainsString("Exif\0\0", $isi);

        $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($isi)) ?: [];

        foreach (['GPSLatitude', 'GPSLongitude', 'Make', 'Model', 'DateTimeOriginal'] as $kunci) {
            $this->assertArrayNotHasKey($kunci, $exif, "Metadata {$kunci} masih tersimpan.");
        }
    }

    public function test_foto_dari_form_tamu_tersimpan_terkompres(): void
    {
        $this->post(route('visitors.store'), [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'host_name' => 'Pak Andi, Blok C2',
            'purpose' => 'Silaturahmi keluarga',
            'ktp' => UploadedFile::fake()->image('ktp.png', 3000, 2000),
            'selfie' => UploadedFile::fake()->image('selfie.png', 2400, 3000),
        ])->assertRedirect(route('guestbook.done'));

        $visitor = Visitor::sole();

        foreach (['ktp_path', 'selfie_path'] as $field) {
            $this->assertGambarTerkompres($visitor->{$field});
        }
    }

    public function test_foto_dari_form_paket_tersimpan_terkompres(): void
    {
        $this->post(route('packages.store'), [
            'courier_name' => 'Rizal Pratama',
            'courier_company' => 'JNE',
            'photo' => UploadedFile::fake()->image('paket.png', 2600, 1800),
            'selfie' => UploadedFile::fake()->image('kurir.png', 2000, 2000),
        ])->assertRedirect(route('guestbook.done'));

        $package = PackageDelivery::sole();

        foreach (['photo_path', 'selfie_path'] as $field) {
            $this->assertGambarTerkompres($package->{$field});
        }
    }

    private function assertGambarTerkompres(string $path): void
    {
        $isi = Storage::disk('local')->get($path);
        $info = getimagesizefromstring($isi);

        $this->assertSame('image/jpeg', $info['mime'], "{$path} bukan JPEG.");
        $this->assertLessThanOrEqual(
            config('guestbook.photo.max_dimension'),
            max($info[0], $info[1]),
            "{$path} melebihi batas dimensi.",
        );
        $this->assertStringNotContainsString('Exif', $isi, "{$path} masih membawa EXIF.");
    }

    private function simpan(UploadedFile $file): string
    {
        return app(PhotoStorageService::class)->store($file, 'uji', 'entri', 'foto');
    }

    /**
     * Berkas JPEG asli berukuran besar — UploadedFile::fake()->image() menghasilkan
     * gambar polos yang terlalu mudah dikompres untuk menguji penyusutan ukuran.
     */
    private function fotoBerukuran(int $width, int $height): UploadedFile
    {
        $im = imagecreatetruecolor($width, $height);

        // Derau acak meniru foto kamera: tidak bisa dikompres secara ekstrem, sehingga
        // perbandingan ukuran sebelum/sesudah jadi bermakna.
        for ($x = 0; $x < $width; $x += 2) {
            for ($y = 0; $y < $height; $y += 2) {
                imagesetpixel($im, $x, $y, imagecolorallocate($im, random_int(0, 255), random_int(0, 255), random_int(0, 255)));
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'uji').'.jpg';
        imagejpeg($im, $path, 95);
        imagedestroy($im);

        return new UploadedFile($path, 'foto.jpg', 'image/jpeg', null, true);
    }

    /**
     * Foto JPEG lengkap dengan blok EXIF berisi koordinat GPS, meniru hasil jepretan
     * kamera HP yang izin lokasinya menyala.
     */
    private function fotoBerGps(): UploadedFile
    {
        $im = imagecreatetruecolor(1200, 900);
        imagefill($im, 0, 0, imagecolorallocate($im, 180, 200, 220));

        $tanpaExif = tempnam(sys_get_temp_dir(), 'gps').'.jpg';
        imagejpeg($im, $tanpaExif, 90);
        imagedestroy($im);

        $jpeg = file_get_contents($tanpaExif);

        // Sisipkan segmen APP1 berisi EXIF tepat setelah penanda SOI (0xFFD8).
        $app1 = $this->segmenExifGps();
        file_put_contents($tanpaExif, substr($jpeg, 0, 2).$app1.substr($jpeg, 2));

        return new UploadedFile($tanpaExif, 'kamera.jpg', 'image/jpeg', null, true);
    }

    /**
     * Segmen APP1 minimal (little-endian) berisi satu entri GPSInfo yang menunjuk ke
     * IFD GPS dengan GPSLatitude dan GPSLongitude.
     */
    private function segmenExifGps(): string
    {
        $tiff = "II\x2a\x00\x08\x00\x00\x00";           // header TIFF, IFD0 di offset 8

        // IFD0: satu entri GPSInfo (tag 0x8825, tipe LONG) menunjuk offset 0x1A
        $tiff .= pack('v', 1);
        $tiff .= pack('vvVV', 0x8825, 4, 1, 0x1A);
        $tiff .= pack('V', 0);                           // tidak ada IFD berikutnya

        // IFD GPS: GPSLatitude (0x0002) dan GPSLongitude (0x0004), masing-masing
        // 3 RATIONAL yang datanya ditaruh setelah IFD.
        $tiff .= pack('v', 2);
        $tiff .= pack('vvVV', 0x0002, 5, 3, 0x3E);
        $tiff .= pack('vvVV', 0x0004, 5, 3, 0x56);
        $tiff .= pack('V', 0);

        // 6°10'30" S, 106°49'20" E — sekitar Jakarta.
        $tiff .= pack('VVVVVV', 6, 1, 10, 1, 30, 1);
        $tiff .= pack('VVVVVV', 106, 1, 49, 1, 20, 1);

        $payload = "Exif\x00\x00".$tiff;

        return "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;
    }
}
