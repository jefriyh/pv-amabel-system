<?php

namespace App\Models;

use App\Http\Controllers\Admin\MediaController;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'previous_security_id',
    'type',
    'attendance_date',
    'day_name',
    'attendance_time',
    'start_time',
    'end_time',
    'latitude',
    'longitude',
    'location_address',
    'selfie_path',
    'status',
    'notes',
])]
class SecurityAttendance extends Model
{
    use HasFactory, HasUlids;

    public const TYPE_MASUK = 'masuk';
    public const TYPE_KELUAR = 'keluar';
    public const TYPE_PATROLI = 'patroli';

    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_MASUK => 'Presensi Masuk',
            self::TYPE_KELUAR => 'Presensi Keluar',
            self::TYPE_PATROLI => 'Log Patroli / Pos',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getTypeLabels()[$this->type] ?? ucfirst($this->type);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function previousSecurity(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_security_id');
    }

    public function getSelfieDataUrlAttribute(): ?string
    {
        if (! empty($this->selfie_path) && Storage::disk('local')->exists($this->selfie_path)) {
            $mime = Storage::disk('local')->mimeType($this->selfie_path) ?? 'image/jpeg';
            $content = base64_encode(Storage::disk('local')->get($this->selfie_path));

            return "data:{$mime};base64,{$content}";
        }

        return null;
    }

    public function getSelfieUrlAttribute(): ?string
    {
        return $this->selfie_data_url ?? MediaController::urlFor($this, 'selfie_path');
    }

    public static function calculateWorkDuration(?string $startTime, ?string $endTime): string
    {
        if (empty($startTime) || empty($endTime)) {
            return '-';
        }

        try {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);

            $startMinutes = $start->hour * 60 + $start->minute;
            $endMinutes = $end->hour * 60 + $end->minute;

            if ($endMinutes <= $startMinutes) {
                $totalMinutes = (24 * 60 - $startMinutes) + $endMinutes;
            } else {
                $totalMinutes = $endMinutes - $startMinutes;
            }

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            if ($minutes > 0) {
                return "{$hours} Jam {$minutes} Menit";
            }

            return "{$hours} Jam";
        } catch (\Throwable $e) {
            return '-';
        }
    }

    public function getWorkDurationAttribute(): string
    {
        return self::calculateWorkDuration($this->start_time, $this->end_time);
    }

    public function getWhatsAppTextAttribute(): string
    {
        $complexName = config('guestbook.complex_name', 'Villa Amabel');
        $securityName = $this->user?->name ?? 'Petugas Security';
        $prevSecurityName = $this->previousSecurity?->name ?? '-';
        $timeStr = Carbon::parse($this->attendance_time)->format('H:i') . ' WIB';
        $dateStr = Carbon::parse($this->attendance_date)->translatedFormat('l, d F Y');
        $durationStr = $this->work_duration;

        $shiftInfo = '-';
        if ($this->start_time && $this->end_time) {
            $start = Carbon::parse($this->start_time)->format('H:i');
            $end = Carbon::parse($this->end_time)->format('H:i');
            $startDate = $this->attendance_date ? Carbon::parse($this->attendance_date) : now();

            if ($end <= $start) {
                $endDate = $startDate->copy()->addDay();
                $shiftInfo = "{$start} ({$startDate->translatedFormat('d M')}) - {$end} ({$endDate->translatedFormat('d M')}) [Shift Malam]";
            } else {
                $shiftInfo = "{$start} - {$end} ({$startDate->translatedFormat('d M')})";
            }
        }

        $detailUrl = url('/internal/security-attendances/' . $this->id);

        $lines = [
            "🛡️ *LOG PRESENSI KEHADIRAN SECURITY*",
            "_{$complexName}_",
            "",
            "👤 *Petugas Bertugas:* {$securityName}",
            "🤝 *Petugas Sebelumnya:* {$prevSecurityName}",
            "📅 *Hari, Tanggal:* {$dateStr}",
            "⏰ *Jam Presensi:* {$timeStr}",
            "🕒 *Jam Tugas Shift:* {$shiftInfo}",
            "⏱️ *Total Durasi Kerja:* {$durationStr}",
        ];

        if (filled($this->notes)) {
            $lines[] = "📝 *Catatan:* {$this->notes}";
        }

        return implode("\n", $lines);
    }

    public function getWhatsAppUrlAttribute(): string
    {
        return 'https://api.whatsapp.com/send?text=' . urlencode($this->whatsapp_text);
    }

    public static function photoFields(): array
    {
        return [
            'selfie_path' => 'Foto selfie presensi',
        ];
    }

    public function hasPhoto(string $field): bool
    {
        return ! empty($this->{$field}) && Storage::disk('local')->exists($this->{$field});
    }

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }
}
