<?php

namespace App\Support;

use App\Models\PackageDelivery;
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
        $lines = [
            '🚶 <b>TAMU MASUK</b> — '.self::escape(config('guestbook.complex_name')),
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
        $lines[] = 'Foto KTP & selfie: <a href="'.self::escape($detailUrl).'">buka di dashboard</a>';

        return implode("\n", $lines);
    }

    public static function forPackage(PackageDelivery $package, string $detailUrl): string
    {
        $lines = [
            '📦 <b>PAKET MASUK KE KOTAK</b> — '.self::escape(config('guestbook.complex_name')),
            '',
            self::row('Kurir', $package->courier_name.' ('.$package->courier_company.')'),
        ];

        if (filled($package->recipient_note)) {
            $lines[] = self::row('Untuk', $package->recipient_note);
        }

        if (filled($package->tracking_number)) {
            $lines[] = self::row('Resi', $package->tracking_number);
        }

        $lines[] = self::row('Waktu', self::time($package->created_at));
        $lines[] = '';
        $lines[] = 'Detail & foto: <a href="'.self::escape($detailUrl).'">buka di dashboard</a>';

        return implode("\n", $lines);
    }

    private static function row(string $label, string $value): string
    {
        return '<b>'.self::escape($label).':</b> '.self::escape($value);
    }

    private static function time(\DateTimeInterface $at): string
    {
        return Carbon::instance($at)->translatedFormat('d M Y, H:i').' WIB';
    }

    /**
     * Telegram parse_mode=HTML hanya mengizinkan sedikit tag; karakter <, >, & dari
     * input tamu harus di-escape atau seluruh pesan ditolak API.
     */
    private static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
