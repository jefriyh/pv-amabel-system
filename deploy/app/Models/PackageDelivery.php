<?php

namespace App\Models;

use App\Models\Concerns\HasGuestbookPhotos;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'courier_name',
    'courier_company',
    'recipient_note',
    'tracking_number',
    'status',
    'received_by',
    'received_at',
    'photo_path',
    'selfie_path',
    'ip_address',
    'user_agent',
])]
class PackageDelivery extends Model
{
    use HasGuestbookPhotos, HasUlids;

    public const STATUS_DITITIPKAN = 'dititipkan';
    public const STATUS_DITERIMA = 'diterima_penghuni';
    public const STATUS_DIKEMBALIKAN = 'dikembalikan';

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_DITITIPKAN => 'Dititipkan di Kotak',
            self::STATUS_DITERIMA => 'Sudah Diterima Penghuni',
            self::STATUS_DIKEMBALIKAN => 'Dikembalikan ke Kurir',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? ucfirst($this->status);
    }

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'photos_purged_at' => 'datetime',
        ];
    }

    public static function photoFields(): array
    {
        return [
            'photo_path' => 'Foto paket',
            'selfie_path' => 'Foto kurir',
        ];
    }
}
