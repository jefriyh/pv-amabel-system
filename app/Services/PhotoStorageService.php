<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * Satu-satunya jalan masuk foto ke penyimpanan.
 *
 * Tidak ada satu pun berkas unggahan yang disimpan apa adanya. Semuanya melewati
 * store() dan selalu mengalami tiga hal:
 *
 *   1. diperkecil sampai sisi terpanjangnya paling besar guestbook.photo.max_dimension;
 *   2. di-encode ulang menjadi JPEG progresif pada kualitas guestbook.photo.quality;
 *   3. metadata EXIF-nya dibuang.
 *
 * Poin ketiga sama pentingnya dengan penghematan tempat: foto kamera HP membawa
 * koordinat GPS rumah tamu, dan itu tidak ada urusannya dengan buku tamu.
 *
 * Kompresi tetap dilakukan di sini walaupun browser sudah mengecilkan foto sebelum
 * mengunggah. Pengecilan di browser hanya optimasi kecepatan unggah dan bisa saja tidak
 * berjalan (JavaScript mati, browser lawas, atau kiriman dibuat manual); server tidak
 * boleh menggantungkan ukuran penyimpanannya pada itikad baik klien.
 */
class PhotoStorageService
{
    public function __construct(private readonly ImageManager $images) {}

    /**
     * Simpan satu foto ke disk privat dan kembalikan path relatifnya.
     *
     * @param  string  $folder  mis. "visitors", "packages"
     * @param  string  $owner  id pemilik (ULID) — dipakai sebagai subfolder
     * @param  string  $name  nama file tanpa ekstensi, mis. "ktp"
     */
    public function store(UploadedFile $file, string $folder, string $owner, string $name): string
    {
        $config = config('guestbook.photo');
        $originalSize = $file->getSize();

        $image = $this->images->read($file->getRealPath());

        // scaleDown hanya mengecilkan; foto yang sudah kecil dibiarkan apa adanya
        // supaya tidak jadi buram karena diperbesar paksa.
        $image->scaleDown(
            width: $config['max_dimension'],
            height: $config['max_dimension'],
        );

        $encoded = (string) $image->toJpeg(
            quality: $config['quality'],
            // Progresif: gambar muncul kasar dulu lalu menajam, dan untuk foto
            // ukurannya biasanya sedikit lebih kecil daripada JPEG biasa.
            progressive: true,
            // Buang EXIF secara eksplisit, tidak menggantungkan diri pada perilaku
            // bawaan encoder yang bisa berubah antar versi.
            strip: true,
        );

        $path = sprintf(
            '%s/%s/%s-%s.jpg',
            $folder,
            $owner,
            $name,
            Str::lower(Str::random(8)),
        );

        Storage::disk('local')->put($path, $encoded);

        Log::debug('Foto guestbook disimpan.', [
            'path' => $path,
            'asal_kb' => (int) round($originalSize / 1024),
            'simpan_kb' => (int) round(strlen($encoded) / 1024),
            'dimensi' => $image->width().'x'.$image->height(),
        ]);

        return $path;
    }

    /**
     * Hapus seluruh folder milik satu entri. Dipakai kalau penyimpanan sebagian
     * berhasil tapi transaksinya gagal, supaya tidak meninggalkan file yatim.
     */
    public function forget(string $folder, string $owner): void
    {
        Storage::disk('local')->deleteDirectory("{$folder}/{$owner}");
    }
}
