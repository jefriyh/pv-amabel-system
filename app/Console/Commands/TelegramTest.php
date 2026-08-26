<?php

namespace App\Console\Commands;

use App\Services\TelegramNotifier;
use Illuminate\Console\Command;
use Throwable;

class TelegramTest extends Command
{
    protected $signature = 'telegram:test';

    protected $description = 'Kirim pesan percobaan ke grup Telegram untuk memastikan token & chat_id sudah benar';

    public function handle(TelegramNotifier $telegram): int
    {
        if (! config('telegram.enabled')) {
            $this->components->error('TELEGRAM_ENABLED masih false di .env. Ubah menjadi true lalu jalankan ulang.');

            return self::FAILURE;
        }

        if (blank(config('telegram.token'))) {
            $this->components->error('TELEGRAM_BOT_TOKEN masih kosong. Ambil token dari @BotFather.');

            return self::FAILURE;
        }

        if (blank(config('telegram.chat_id'))) {
            $this->components->error('TELEGRAM_CHAT_ID masih kosong. Lihat panduannya di README bagian "Setup Telegram".');

            return self::FAILURE;
        }

        // Cek token lebih dulu supaya pesan errornya jelas: token salah vs chat_id salah.
        try {
            $bot = $telegram->me();
            $this->components->info("Token valid. Bot: @{$bot['username']} ({$bot['first_name']}).");
        } catch (Throwable $e) {
            $this->components->error('Token bot ditolak Telegram: '.$e->getMessage());
            $this->line('  Periksa kembali TELEGRAM_BOT_TOKEN, atau buat ulang lewat /revoke di @BotFather.');

            return self::FAILURE;
        }

        try {
            $telegram->send(
                "\u{2705} <b>Tes koneksi Guestbook</b>\n\n"
                .'Kalau pesan ini muncul di grup, notifikasi tamu dan paket sudah siap dipakai.'
            );
        } catch (Throwable $e) {
            $this->components->error('Gagal mengirim ke grup: '.$e->getMessage());
            $this->line('  Penyebab tersering:');
            $this->line('  - chat_id salah (harus angka negatif untuk grup, mis. -1001234567890)');
            $this->line('  - bot belum dimasukkan ke grup, atau sudah dikeluarkan');
            $this->line('  - grup memakai Topics tapi TELEGRAM_THREAD_ID belum diisi');

            return self::FAILURE;
        }

        $this->components->info('Pesan percobaan terkirim. Silakan cek grup Telegram Anda.');

        return self::SUCCESS;
    }
}
