<?php
$t = microtime(true);
app(App\Services\TelegramNotifier::class)->sendQuietly('Uji kecepatan koneksi Telegram.');
printf("kirim telegram : %.2f detik\n", microtime(true) - $t);

$t = microtime(true);
$file = new Illuminate\Http\UploadedFile('/var/www/html/storage/app/uji.jpg', 'uji.jpg', 'image/jpeg', null, true);
app(App\Services\PhotoStorageService::class)->store($file, 'bench', 'tmp', 'x');
printf("proses 1 foto  : %.2f detik\n", microtime(true) - $t);
Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('bench');
