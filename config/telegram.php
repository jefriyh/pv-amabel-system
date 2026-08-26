<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Saklar utama
    |--------------------------------------------------------------------------
    |
    | Kalau false, notifikasi tidak dikirim sama sekali (berguna saat development
    | atau kalau grup Telegram belum siap). Form tetap bisa disubmit seperti biasa.
    |
    */

    'enabled' => env('TELEGRAM_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Kredensial bot
    |--------------------------------------------------------------------------
    |
    | token    : dari @BotFather (format "8123456789:AAH...").
    | chat_id  : id grup tujuan, biasanya negatif (mis. -1001234567890).
    | thread_id: hanya diisi kalau grup memakai fitur Topics.
    |
    */

    'token' => env('TELEGRAM_BOT_TOKEN'),

    'chat_id' => env('TELEGRAM_CHAT_ID'),

    'thread_id' => env('TELEGRAM_THREAD_ID'),

    /*
    |--------------------------------------------------------------------------
    | Endpoint & jaringan
    |--------------------------------------------------------------------------
    |
    | api_url dibuat bisa diubah supaya gampang diarahkan ke mock server saat testing.
    | Kalau server tidak bisa menjangkau api.telegram.org secara langsung, isi
    | HTTPS_PROXY di .env — Guzzle akan otomatis memakainya.
    |
    */

    'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),

    // Pesan dikirim langsung saat tamu menekan kirim, jadi tamu ikut menunggu
    // selama ini. Sengaja pendek: lebih baik notifikasi menyusul lewat log daripada
    // tamu berdiri di gerbang menatap layar yang berputar.
    'timeout' => (int) env('TELEGRAM_TIMEOUT', 8),

];
