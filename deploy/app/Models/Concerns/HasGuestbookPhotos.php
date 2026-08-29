<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Perilaku yang sama untuk Visitor dan PackageDelivery: menyimpan beberapa path foto
 * di disk privat, lalu menghapus file-nya saat masa retensi habis tanpa ikut
 * menghapus baris rekapnya.
 */
trait HasGuestbookPhotos
{
    /**
     * Nama kolom yang berisi path foto, beserta label manusiawinya.
     *
     * @return array<string, string>
     */
    abstract public static function photoFields(): array;

    public function hasPhoto(string $field): bool
    {
        return filled($this->{$field}) && Storage::disk('local')->exists($this->{$field});
    }

    /**
     * Hapus semua file foto milik baris ini. Baris datanya sendiri tetap ada.
     */
    public function purgePhotos(): int
    {
        $deleted = 0;

        foreach (array_keys(static::photoFields()) as $field) {
            if (blank($this->{$field})) {
                continue;
            }

            if (Storage::disk('local')->delete($this->{$field})) {
                $deleted++;
            }

            $this->{$field} = null;
        }

        $this->photos_purged_at = now();
        $this->save();

        return $deleted;
    }
}
