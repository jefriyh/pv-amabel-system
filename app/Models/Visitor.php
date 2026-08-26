<?php

namespace App\Models;

use App\Models\Concerns\HasGuestbookPhotos;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'phone',
    'host_name',
    'purpose',
    'ktp_path',
    'selfie_path',
    'ip_address',
    'user_agent',
])]
class Visitor extends Model
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
            'ktp_path' => 'Foto KTP',
            'selfie_path' => 'Foto selfie',
        ];
    }
}
