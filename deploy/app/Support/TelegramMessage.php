<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\PackageDelivery;
use App\Models\SecurityAttendance;
use App\Models\Visitor;
use Illuminate\Support\Carbon;

/**
 * Penyusun teks notifikasi Telegram.
 *
 * Catatan privasi: foto KTP dan selfie TIDAK pernah ikut dikirim ke grup. Pesan hanya
 * memuat ringkasan dan tautan ke halaman detail di dashboard yang butuh login.
 */
class TelegramMessage
{
    public static function forVisitor(Visitor $visitor, string $detailUrl): string
    {
        $publicUrl = self::resolvePublicUrl($detailUrl);

        $lines = [
            '🚶 <b>TAMU MASUK</b> - ' . self::escape(config('guestbook.complex_name')),
            '',
            self::row('Nama', $visitor->name),
        ];

        if (filled($visitor->phone)) {
            $lines[] = self::row('No. HP', $visitor->phone);
        }

        if (filled($visitor->host_name)) {
            $lines[] = self::row('Menemui', $visitor->host_name);
        }

        $lines[] = self::row('Keperluan', $visitor->purpose);
        $lines[] = self::row('Waktu', self::time($visitor->created_at));
        $lines[] = '';
        $lines[] = '🔗 <a href="' . self::escapeUrl($publicUrl) . '"><b>👉 Klik di Sini untuk Lihat Detail Tamu</b></a>';

        return implode("\n", $lines);
    }

    public static function forPackage(PackageDelivery $package, string $detailUrl): string
    {
        $publicUrl = self::resolvePublicUrl($detailUrl);

        $lines = [
            '📦 <b>PAKET MASUK KE KOTAK</b> - ' . self::escape(config('guestbook.complex_name')),
            '',
            self::row('Kurir', $package->courier_name . ' (' . $package->courier_company . ')'),
        ];

        if (filled($package->recipient_note)) {
            $lines[] = self::row('Untuk', $package->recipient_note);
        }

        if (filled($package->tracking_number)) {
            $lines[] = self::row('Resi', $package->tracking_number);
        }

        $lines[] = self::row('Waktu', self::time($package->created_at));
        $lines[] = '';
        $lines[] = '🔗 <a href="' . self::escapeUrl($publicUrl) . '"><b>👉 Klik di Sini untuk Lihat Detail Paket</b></a>';

        return implode("\n", $lines);
    }

    public static function forAttendance(SecurityAttendance $attendance, string $detailUrl): string
    {
        $publicUrl = self::resolvePublicUrl($detailUrl);
        $complexName = config('guestbook.complex_name', 'Villa Amabel');
        $securityName = $attendance->user?->name ?? 'Petugas Security';
        $timeStr = Carbon::parse($attendance->attendance_time)->format('H:i') . ' WIB';
        $dateStr = Carbon::parse($attendance->attendance_date)->translatedFormat('l, d F Y');

        $shiftInfo = '-';
        if ($attendance->start_time && $attendance->end_time) {
            $start = Carbon::parse($attendance->start_time)->format('H:i');
            $end = Carbon::parse($attendance->end_time)->format('H:i');
            $startDate = $attendance->attendance_date ? Carbon::parse($attendance->attendance_date) : now();

            if ($end <= $start) {
                $endDate = $startDate->copy()->addDay();
                $shiftInfo = "{$start} ({$startDate->translatedFormat('d M')}) - {$end} ({$endDate->translatedFormat('d M')}) [Shift Malam / Lewat Hari]";
            } else {
                $shiftInfo = "{$start} - {$end} ({$startDate->translatedFormat('d M')})";
            }
        }

        $lines = [
            '🛡️ <b>LOG PRESENSI KEHADIRAN SECURITY</b> - ' . self::escape($complexName),
            '',
            self::row('Petugas Bertugas', $securityName),
            self::row('Petugas Sebelumnya', $attendance->previousSecurity?->name ?? '-'),
            self::row('Hari, Tanggal', $dateStr),
            self::row('Jam Presensi', $timeStr),
            self::row('Jam Tugas Shift', $shiftInfo),
            self::row('Total Durasi Kerja', $attendance->work_duration),
        ];

        if (filled($attendance->location_address)) {
            $lines[] = self::row('Lokasi Pos', $attendance->location_address);
        }

        if ($attendance->latitude && $attendance->longitude) {
            $mapsUrl = "https://www.google.com/maps?q={$attendance->latitude},{$attendance->longitude}";
            $lines[] = '📍 <b>Koordinat GPS:</b> <a href="' . self::escapeUrl($mapsUrl) . '">' . $attendance->latitude . ', ' . $attendance->longitude . ' (Buka Maps)</a>';
        }

        if (filled($attendance->notes)) {
            $lines[] = self::row('Catatan', $attendance->notes);
        }

        $lines[] = '';
        $lines[] = '🔗 <a href="' . self::escapeUrl($publicUrl) . '"><b>👉 Klik di Sini untuk Lihat Detail Presensi</b></a>';

        return implode("\n", $lines);
    }

    public static function forLeaveRequest(LeaveRequest $leave, string $detailUrl): string
    {
        $publicUrl = self::resolvePublicUrl($detailUrl);
        $complexName = config('guestbook.complex_name', 'Villa Amabel');
        $userName = $leave->user?->name ?? 'Petugas Security';
        $typeLabel = $leave->type_label;
        $totalDays = $leave->total_days ?? 1;
        $datesFormatted = $leave->formatted_selected_dates;
        $remainingQuota = $leave->user?->remaining_leave_quota;

        $icon = match ($leave->type) {
            LeaveRequest::TYPE_CUTI => '🏖️',
            LeaveRequest::TYPE_SAKIT => '🏥',
            LeaveRequest::TYPE_IZIN_DARURAT => '⚠️',
            default => '📋',
        };

        $lines = [
            "{$icon} <b>PENGAJUAN " . strtoupper(self::escape($typeLabel)) . ' SECURITY</b> - ' . self::escape($complexName),
            '',
            self::row('Nama Petugas', $userName),
            self::row('Jenis Pengajuan', $typeLabel),
            self::row('Tanggal Dipilih', $datesFormatted),
            self::row('Total Durasi', "{$totalDays} Hari"),
        ];

        if ($leave->type === LeaveRequest::TYPE_CUTI && $remainingQuota !== null) {
            $lines[] = self::row('Sisa Kuota Cuti', "{$remainingQuota} Hari");
        }

        if (filled($leave->reason)) {
            $lines[] = self::row('Alasan / Keterangan', $leave->reason);
        }

        if (filled($leave->attachment_path)) {
            $lines[] = self::row('Dokumen Lampiran', 'Ada (Surat Dokter / Bukti Pendukung)');
        }

        $lines[] = self::row('Status', '⏳ Menunggu Persetujuan Pengurus');
        $lines[] = '';
        $lines[] = '🔗 <a href="' . self::escapeUrl($publicUrl) . '"><b>👉 Klik di Sini untuk Review & Persetujuan</b></a>';

        return implode("\n", $lines);
    }

    /**
     * Pastikan URL link selalu mengarah ke domain publik HTTPS yang valid dan bukan localhost.
     */
    public static function resolvePublicUrl(string $pathOrUrl): string
    {
        $defaultHost = 'https://amabel.web.id';
        $appUrl = config('app.url', $defaultHost);

        if (empty($appUrl) || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $appUrl = $defaultHost;
        }

        $appUrl = rtrim($appUrl, '/');

        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            $parsed = parse_url($pathOrUrl);
            $host = $parsed['host'] ?? '';
            if ($host === 'localhost' || $host === '127.0.0.1' || empty($host)) {
                $path = ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
                return $appUrl . '/' . ltrim($path, '/');
            }
            return $pathOrUrl;
        }

        return $appUrl . '/' . ltrim($pathOrUrl, '/');
    }

    private static function row(string $label, string $value): string
    {
        return '<b>' . self::escape($label) . ':</b> ' . self::escape($value);
    }

    private static function time(\DateTimeInterface $at): string
    {
        return Carbon::instance($at)->translatedFormat('d M Y, H:i') . ' WIB';
    }

    /**
     * Telegram parse_mode=HTML hanya mengizinkan sedikit tag; karakter <, >, & dari
     * input tamu harus di-escape atau seluruh pesan ditolak API.
     */
    private static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function escapeUrl(?string $url): string
    {
        return htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
