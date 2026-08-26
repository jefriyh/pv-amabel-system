<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * Satu-satunya jalan masuk foto ke penyimpanan.
 *
 * Semua foto di-encode ulang menjadi JPEG. Encode ulang ini penting bukan hanya untuk
 * menghemat tempat: ia membuang seluruh metadata EXIF bawaan kamera HP — termasuk
 * koordinat GPS rumah tamu — yang tidak ada urusannya dengan buku tamu.
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

        $image = $this->images->read($file->getRealPath());

        // scaleDown hanya mengecilkan; foto yang sudah kecil dibiarkan apa adanya
        // supaya tidak jadi buram karena diperbesar paksa.
        $image->scaleDown(
            width: $config['max_dimension'],
            height: $config['max_dimension'],
        );

        $path = sprintf(
            '%s/%s/%s-%s.jpg',
            $folder,
            $owner,
            $name,
            Str::lower(Str::random(8)),
        );

        Storage::disk('local')->put(
            $path,
            (string) $image->toJpeg(quality: $config['quality']),
        );

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
