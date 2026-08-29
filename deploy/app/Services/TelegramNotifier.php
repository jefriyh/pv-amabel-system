<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Pengirim pesan ke Telegram Bot API.
 *
 * Sengaja tidak memakai package pihak ketiga: yang dibutuhkan hanya satu endpoint
 * (sendMessage), dan client HTTP bawaan Laravel sudah menangani retry serta proxy
 * (lewat env HTTPS_PROXY) tanpa dependensi tambahan.
 *
 * Pesan dikirim langsung saat form disubmit, bukan lewat antrean. Konsekuensinya
 * tamu ikut menunggu jawaban Telegram, jadi timeout-nya sengaja pendek dan
 * kegagalannya ditangani lewat sendQuietly() agar tidak pernah menggagalkan submit.
 */
class TelegramNotifier
{
    public function isConfigured(): bool
    {
        return (bool) config('telegram.enabled')
            && filled(config('telegram.token'))
            && filled(config('telegram.chat_id'));
    }

    /**
     * Kirim pesan HTML ke grup yang dikonfigurasi.
     *
     * @throws RuntimeException kalau Telegram menolak pesannya. Dipakai langsung oleh
     *                          command `telegram:test` supaya penyebab kegagalannya
     *                          terlihat jelas di terminal.
     */
    public function send(string $html): void
    {
        if (! $this->isConfigured()) {
            Log::info('Notifikasi Telegram dilewati: TELEGRAM_ENABLED/TOKEN/CHAT_ID belum lengkap.');

            return;
        }

        $payload = [
            'chat_id' => config('telegram.chat_id'),
            'text' => $html,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if (filled($threadId = config('telegram.thread_id'))) {
            $payload['message_thread_id'] = (int) $threadId;
        }

        $response = Http::timeout(config('telegram.timeout'))
            // Satu percobaan ulang untuk gangguan sesaat. Tidak lebih dari itu:
            // setiap percobaan menambah waktu tunggu tamu di depan gerbang.
            ->retry(2, 250, throw: false)
            ->asJson()
            ->post($this->endpoint('sendMessage'), $payload);

        if ($response->failed() || $response->json('ok') !== true) {
            throw new RuntimeException(sprintf(
                'Telegram menolak pesan (HTTP %d): %s',
                $response->status(),
                $response->json('description') ?? $response->body(),
            ));
        }
    }

    /**
     * Versi send() yang tidak pernah melempar exception.
     *
     * Dipakai controller form tamu/paket: data tamu sudah tersimpan dengan selamat, dan
     * tamu tidak boleh melihat halaman error hanya karena Telegram sedang bermasalah.
     * Kegagalannya dicatat di log supaya pengurus bisa menelusurinya belakangan.
     *
     * @return bool true kalau pesan benar-benar terkirim
     */
    public function sendQuietly(string $html): bool
    {
        try {
            $this->send($html);

            return $this->isConfigured();
        } catch (Throwable $e) {
            Log::error('Gagal mengirim notifikasi Telegram: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Ambil info bot — dipakai command `telegram:test` untuk memastikan token benar
     * sebelum menyalahkan chat_id.
     *
     * @return array<string, mixed>
     */
    public function me(): array
    {
        $response = Http::timeout(config('telegram.timeout'))->get($this->endpoint('getMe'));

        if ($response->failed() || $response->json('ok') !== true) {
            throw new RuntimeException(sprintf(
                'Gagal memanggil getMe (HTTP %d): %s',
                $response->status(),
                $response->json('description') ?? $response->body(),
            ));
        }

        return $response->json('result');
    }

    private function endpoint(string $method): string
    {
        return sprintf(
            '%s/bot%s/%s',
            rtrim(config('telegram.api_url'), '/'),
            config('telegram.token'),
            $method,
        );
    }
}
