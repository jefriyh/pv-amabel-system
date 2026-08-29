<?php

namespace App\Console\Commands;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateQrCode extends Command
{
    protected $signature = 'guestbook:qr {--url= : URL tujuan (default APP_URL)}';

    protected $description = 'Buat file QR code untuk ditempel di gerbang / pos satpam';

    public function handle(): int
    {
        $url = $this->option('url') ?: rtrim(config('app.url'), '/').'/';

        if (! str_starts_with($url, 'https://')) {
            // Bukan error fatal — saat development memang http. Tapi tamu tidak akan
            // bisa memakai kamera HP-nya kalau QR ini mengarah ke http.
            $this->components->warn("URL masih {$url}. Kamera HP hanya aktif di https, jadi QR ini belum layak dicetak.");
        }

        // ECC level Q: masih terbaca walau stiker QR-nya nanti kotor atau tergores.
        $svg = (new QRCode(new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::Q,
            'scale' => 10,
            'imageBase64' => false,
            'svgUseFillAttributes' => false,
            'addQuietzone' => true,
        ])))->render($url);

        $path = 'qr/guestbook.svg';
        Storage::disk('local')->put($path, $svg);

        $this->components->info('QR code dibuat: '.Storage::disk('local')->path($path));
        $this->line('  Mengarah ke: '.$url);
        $this->line('  Cetak file ini dan tempel di gerbang beserta tulisan "Scan untuk isi buku tamu".');

        return self::SUCCESS;
    }
}
