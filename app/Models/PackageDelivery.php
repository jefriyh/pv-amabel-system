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
    'photo_path',
    'selfie_path',
    'ip_address',
    'user_agent',
])]
class PackageDelivery extends Model
{
    use HasGuestbookPhotos, HasUlids;

    protected function casts(): array
    {
        return [
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
