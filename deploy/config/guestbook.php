<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retensi foto
    |--------------------------------------------------------------------------
    |
    | Foto KTP & selfie adalah data pribadi. Setelah sekian hari, file fotonya
    | dihapus otomatis oleh command `guestbook:purge-photos` (dijalankan scheduler),
    | sementara baris datanya tetap tersimpan sebagai rekap kunjungan.
    |
    */

    'photo_retention_days' => (int) env('GUESTBOOK_PHOTO_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Pemrosesan foto
    |--------------------------------------------------------------------------
    |
    | Semua foto di-resize dan di-encode ulang sebagai JPEG. Encode ulang ini
    | sekaligus membuang metadata EXIF (termasuk koordinat GPS) dari kamera HP.
    |
    */

    'photo' => [
        'max_dimension' => 1600,
        'quality' => 80,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pilihan ekspedisi pada form paket
    |--------------------------------------------------------------------------
    */

    'couriers' => [
        'JNE',
        'J&T Express',
        'SiCepat',
        'Anteraja',
        'SPX / Shopee Express',
        'Ninja Xpress',
        'Lazada Logistics',
        'Pos Indonesia',
        'TIKI',
        'GoSend / GrabExpress',
        'Lainnya',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama komplek — dipakai di judul form dan pesan Telegram
    |--------------------------------------------------------------------------
    */

    'complex_name' => env('GUESTBOOK_COMPLEX_NAME', 'Villa Amabel'),
    'portal_name' => env('GUESTBOOK_PORTAL_NAME', 'Villa Amabel - Internal Portal'),

];
