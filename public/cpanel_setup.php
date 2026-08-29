<?php
/**
 * Script Pembantu Setup Otomatis Villa Amabel di cPanel (Tanpa SSH)
 *
 * Cara Menggunakan:
 * 1. Buka di browser: https://domain-anda.com/cpanel_setup.php
 * 2. Script ini akan memvalidasi PHP, generate APP_KEY, migrasi database,
 *    seeding akun default (Superadmin, Pengurus, Security), optimasi cache,
 *    dan menghapus dirinya sendiri demi keamanan.
 */

define('LARAVEL_START', microtime(true));

$basePath = dirname(__DIR__);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Setup Villa Amabel cPanel</title>";
echo "<style>body{font-family:ui-monospace,monospace;background:#0f172a;color:#f8fafc;padding:2rem;line-height:1.6}pre{background:#1e293b;padding:1.5rem;border-radius:0.75rem;border:1px solid #334155;overflow-x:auto}h2{color:#38bdf8}</style></head><body>";
echo "<h2>🛡️ Villa Amabel Integrated System — cPanel Auto Setup</h2><pre>";

// 1. Validasi Versi PHP & Ekstensi
$fatal = [];

if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    $fatal[] = 'Versi PHP Anda adalah ' . PHP_VERSION . '. Diperlukan PHP 8.2 atau lebih baru di cPanel > MultiPHP Manager / Select PHP Version.';
}

$required = [
    'pdo_mysql' => 'Koneksi database MySQL',
    'mbstring'  => 'Pemrosesan string & UTF-8',
    'openssl'   => 'Enkripsi data & session',
    'fileinfo'  => 'Deteksi MIME-type foto KTP/selfie',
    'gd'        => 'Intervention Image (resize & kompresi foto)',
    'curl'      => 'Pengiriman notifikasi Telegram API',
    'tokenizer' => 'Blade template engine',
    'xml'       => 'XML/DOM support',
    'zip'       => 'Pustaka ekstraksi zip',
    'ctype'     => 'Validasi karakter',
];

foreach ($required as $ext => $why) {
    if (! extension_loaded($ext)) {
        $fatal[] = "Ekstensi PHP '{$ext}' belum aktif (dibutuhkan untuk {$why}). Aktifkan di cPanel > Select PHP Version > Extensions.";
    }
}

if (! is_file($basePath . '/vendor/autoload.php')) {
    $fatal[] = 'vendor/autoload.php tidak ditemukan. Pastikan seluruh isi file zip telah diekstrak dengan lengkap.';
}

if (! is_file($basePath . '/.env')) {
    $fatal[] = 'Berkas .env belum ada. Salin .env.production.example menjadi .env melalui cPanel File Manager, isi konfigurasi database, lalu muat ulang halaman ini.';
}

foreach (['storage', 'bootstrap/cache'] as $writable) {
    $path = $basePath . '/' . $writable;
    if (is_dir($path) && ! is_writable($path)) {
        $fatal[] = "Direktori {$writable} tidak memiliki izin tulis (writable). Set permission ke 755 atau 775 di cPanel File Manager.";
    }
}

if ($fatal !== []) {
    echo "❌ SETUP DIHENTIKAN. Silakan perbaiki hal-hal berikut:\n\n";
    foreach ($fatal as $i => $message) {
        echo '  ' . ($i + 1) . ". {$message}\n";
    }
    echo "</pre></body></html>";
    exit;
}

// Hapus cache lama (termasuk blade-icons.php yang menyimpan path komputer asal)
foreach (glob($basePath . '/bootstrap/cache/*.php') as $cacheFile) {
    @unlink($cacheFile);
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// WAJIB bootstrap kernel terlebih dahulu agar config, providers, dan facades dimuat
$kernel->bootstrap();

$run = function (string $command, array $args = []) use ($kernel) {
    $kernel->call($command, $args);
    echo $kernel->output();
};

try {
    echo "1. Memeriksa APP_KEY...\n";
    $envPath = $basePath . '/.env';
    $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
    
    if (blank(config('app.key')) || !preg_match('/^APP_KEY=base64:.+$/m', $envContent)) {
        echo "   Membuat APP_KEY baru...\n";
        $run('key:generate', ['--force' => true]);
    } else {
        echo "   APP_KEY sudah terpasang.\n";
    }

    echo "\n2. Membersihkan cache lama & ikon...\n";
    $run('optimize:clear');
    try {
        $run('icons:clear');
        $run('view:clear');
    } catch (\Throwable $iconErr) {
        // Abaikan jika command tidak tersedia
    }

    echo "\n3. Menjalankan Database Migration...\n";
    $run('migrate', ['--force' => true]);

    echo "\n4. Menyiapkan akun pengguna awal (Superadmin, Pengurus, Security)...\n";
    $run('db:seed', ['--class' => 'Database\\Seeders\\UserSeeder', '--force' => true]);

    echo "\n5. Menyiapkan Symbolic Link Storage...\n";
    $targetPath = storage_path('app/public');
    $linkPath = public_path('storage');

    if (! is_dir($targetPath)) {
        @mkdir($targetPath, 0755, true);
    }

    if (is_link($linkPath)) {
        @unlink($linkPath);
    } elseif (is_dir($linkPath)) {
        @rmdir($linkPath);
    }

    try {
        $run('storage:link', ['--force' => true]);
    } catch (\Throwable $linkErr) {
        if (@symlink($targetPath, $linkPath)) {
            echo "   ✅ Symlink storage berhasil dibuat secara manual.\n";
        } else {
            echo "   ℹ️ Symlink storage dilewati (hosting membatasi symlink).\n";
        }
    }

    echo "\n6. Mengoptimasi cache produksi (Config, Route, View)...\n";
    $run('config:cache');
    $run('route:cache');
    $run('view:cache');

    echo "\n==========================================================\n";
    echo "🎉 SETUP BERHASIL SELESAI!\n";
    echo "==========================================================\n\n";
    echo "Akun Default yang Siap Digunakan:\n";
    echo "1. Super Admin : superadmin@amabel.id (Password: password)\n";
    echo "2. Pengurus    : pengurus@amabel.id   (Password: password)\n";
    echo "3. Security    : security@amabel.id   (Password: password)\n\n";
    echo "⚠️ CATATAN KEAMANAN:\n";
    echo "- Segera login ke portal internal dan ganti password akun di atas!\n";
    echo "- Pastikan APP_DEBUG bernilai false di berkas .env Anda.\n\n";

    echo "🔒 Menghapus script cpanel_setup.php demi keamanan...\n";
    @unlink(__FILE__);
    echo "Selesai. Silakan akses aplikasi Anda sekarang.";

} catch (\Throwable $e) {
    echo "\n❌ Terjadi Kesalahan:\n" . $e->getMessage() . "\n\n";
    echo "Berkas cpanel_setup.php TIDAK dihapus agar Anda dapat memperbaikinya\n";
    echo "dan memuat ulang halaman ini.\n";
}

echo "</pre></body></html>";
