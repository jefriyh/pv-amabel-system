<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'type',
    'selected_dates',
    'start_date',
    'end_date',
    'total_days',
    'reason',
    'attachment_path',
    'status',
    'approved_by',
    'approved_at',
    'approval_notes',
])]
class LeaveRequest extends Model
{
    use HasFactory, HasUlids;

    public const TYPE_CUTI = 'cuti';
    public const TYPE_IZIN_DARURAT = 'izin_darurat';
    public const TYPE_SAKIT = 'sakit';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_CUTI => 'Cuti',
            self::TYPE_IZIN_DARURAT => 'Izin Darurat',
            self::TYPE_SAKIT => 'Sakit',
        ];
    }

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getTypeLabels()[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedSelectedDatesAttribute(): string
    {
        if (! empty($this->selected_dates) && is_array($this->selected_dates)) {
            $dates = collect($this->selected_dates)->sort()->values();

            return $dates->map(fn ($d) => Carbon::parse($d)->format('d M Y'))->implode(', ');
        }

        if ($this->start_date && $this->end_date) {
            if ($this->start_date->equalTo($this->end_date)) {
                return $this->start_date->format('d M Y');
            }

            return $this->start_date->format('d M Y') . ' - ' . $this->end_date->format('d M Y');
        }

        return '-';
    }

    public static function photoFields(): array
    {
        return [
            'attachment_path' => 'Lampiran / Dokumen Bukti',
        ];
    }

    public function hasPhoto(string $field): bool
    {
        return ! empty($this->{$field}) && Storage::disk('local')->exists($this->{$field});
    }

    protected function casts(): array
    {
        return [
            'selected_dates' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'integer',
            'approved_at' => 'datetime',
        ];
    }
}
