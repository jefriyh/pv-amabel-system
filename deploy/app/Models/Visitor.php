<?php

namespace App\Models;

use App\Models\Concerns\HasGuestbookPhotos;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'phone',
    'host_name',
    'purpose',
    'status',
    'approved_by',
    'approved_at',
    'approval_notes',
    'ktp_path',
    'selfie_path',
    'ip_address',
    'user_agent',
])]
class Visitor extends Model
{
    use HasGuestbookPhotos, HasUlids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CHECKED_IN = 'checked_in';

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Approval',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CHECKED_IN => 'Sudah Masuk',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
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
