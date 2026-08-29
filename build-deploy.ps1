# ==============================================================================
# Villa Amabel — Integrated System
# Script Build & Export Deployment cPanel Shared Hosting (Windows PowerShell)
#
# Cara Menjalankan di PowerShell:
#   .\build-deploy.ps1
#
# Hasilnya: deploy.zip di root proyek yang siap diunggah ke cPanel File Manager.
# ==============================================================================

$ErrorActionPreference = "Stop"

$WorkDir = $PSScriptRoot
$DeployDir = Join-Path $WorkDir "deploy"
$ZipFile = Join-Path $WorkDir "deploy.zip"

Write-Host "🚀 Memulai proses build & export cPanel untuk Villa Amabel..." -ForegroundColor Cyan

# ------------------------------------------------------------------------------
# 0. Pemeriksaan Awal
# ------------------------------------------------------------------------------
Write-Host "🔎 Memeriksa prasyarat build..." -ForegroundColor Yellow

foreach ($cmd in @("php", "composer", "npm")) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) {
        Write-Error "Command '$cmd' tidak ditemukan di PATH sistem ini."
    }
}

# ------------------------------------------------------------------------------
# 1. Bersihkan Folder Deploy & File ZIP Lama
# ------------------------------------------------------------------------------
Write-Host "🧹 Membersihkan output deploy sebelumnya..." -ForegroundColor Yellow
if (Test-Path $DeployDir) {
    Remove-Item -Recurse -Force $DeployDir
}
New-Item -ItemType Directory -Path $DeployDir | Out-Null

if (Test-Path $ZipFile) {
    Remove-Item -Force $ZipFile
}

# ------------------------------------------------------------------------------
# 2. Build Asset Frontend Production (Vite / Tailwind)
# ------------------------------------------------------------------------------
Write-Host "📦 Mengompilasi asset frontend (Vite build — PRODUCTION)..." -ForegroundColor Cyan

$env:NODE_ENV = "production"
npm run build

if (-not (Test-Path (Join-Path $WorkDir "public\build\manifest.json"))) {
    Write-Error "public/build/manifest.json tidak ditemukan! Build frontend gagal."
}
Write-Host "✅ Frontend production build berhasil." -ForegroundColor Green

# ------------------------------------------------------------------------------
# 3. Salin Berkas Aplikasi ke Folder Deploy
# ------------------------------------------------------------------------------
Write-Host "📋 Menyalin berkas aplikasi ke folder deploy..." -ForegroundColor Yellow

$copyDirs = @("app", "bootstrap", "config", "database", "public", "resources", "routes")
foreach ($dir in $copyDirs) {
    $src = Join-Path $WorkDir $dir
    $dest = Join-Path $DeployDir $dir
    if (Test-Path $src) {
        Copy-Item -Recurse -Force -Path $src -Destination $dest
    }
}

Copy-Item -Force (Join-Path $WorkDir "artisan") (Join-Path $DeployDir "artisan")
Copy-Item -Force (Join-Path $WorkDir "composer.json") (Join-Path $DeployDir "composer.json")
if (Test-Path (Join-Path $WorkDir "composer.lock")) {
    Copy-Item -Force (Join-Path $WorkDir "composer.lock") (Join-Path $DeployDir "composer.lock")
}

# Bersihkan symlink & temporary files
if (Test-Path (Join-Path $DeployDir "public\storage")) {
    Remove-Item -Recurse -Force (Join-Path $DeployDir "public\storage")
}
if (Test-Path (Join-Path $DeployDir "public\hot")) {
    Remove-Item -Force (Join-Path $DeployDir "public\hot")
}
if (Test-Path (Join-Path $DeployDir ".env")) {
    Remove-Item -Force (Join-Path $DeployDir ".env")
}

# ------------------------------------------------------------------------------
# 4. Buang Cache Bootstrap Milik Lingkungan Pengembangan
# ------------------------------------------------------------------------------
Write-Host "🧽 Membersihkan cache bootstrap bawaan dev..." -ForegroundColor Yellow
$cacheFiles = Get-ChildItem -Path (Join-Path $DeployDir "bootstrap\cache") -Filter "*.php" -File
foreach ($file in $cacheFiles) {
    Remove-Item -Force $file.FullName
}

# ------------------------------------------------------------------------------
# 5. Install Dependensi Composer Production
# ------------------------------------------------------------------------------
Write-Host "⚙️ Menginstall dependensi Composer Production di folder deploy..." -ForegroundColor Cyan

if (Test-Path (Join-Path $WorkDir "vendor")) {
    Write-Host "   Menyalin vendor lokal sebagai basis awal..." -ForegroundColor Gray
    Copy-Item -Recurse -Force -Path (Join-Path $WorkDir "vendor") -Destination (Join-Path $DeployDir "vendor")
}

composer install --no-dev --no-interaction --no-scripts --no-progress --optimize-autoloader --working-dir="$DeployDir"

# ------------------------------------------------------------------------------
# 5b. Pastikan Seluruh SVG Heroicons Tersalin Lengkap ke resources/svg/heroicons
# ------------------------------------------------------------------------------
Write-Host "🎨 Memastikan seluruh ikon Heroicons SVG tersalin lengkap ke resources/svg/heroicons..." -ForegroundColor Cyan
$deploySvgDir = Join-Path $DeployDir "resources\svg\heroicons"
if (-not (Test-Path $deploySvgDir)) {
    New-Item -ItemType Directory -Path $deploySvgDir -Force | Out-Null
}
$vendorSvgDir = Join-Path $DeployDir "vendor\blade-ui-kit\blade-heroicons\resources\svg"
if (Test-Path $vendorSvgDir) {
    Copy-Item -Path (Join-Path $vendorSvgDir "*.svg") -Destination $deploySvgDir -Force -ErrorAction SilentlyContinue
}
$localVendorSvgDir = Join-Path $WorkDir "vendor\blade-ui-kit\blade-heroicons\resources\svg"
if (Test-Path $localVendorSvgDir) {
    Copy-Item -Path (Join-Path $localVendorSvgDir "*.svg") -Destination $deploySvgDir -Force -ErrorAction SilentlyContinue
}
$localResSvgDir = Join-Path $WorkDir "resources\svg\heroicons"
if (Test-Path $localResSvgDir) {
    Copy-Item -Path (Join-Path $localResSvgDir "*.svg") -Destination $deploySvgDir -Force -ErrorAction SilentlyContinue
}

# ------------------------------------------------------------------------------
# 6. Siapkan Struktur Direktori Storage Lengkap
# ------------------------------------------------------------------------------
Write-Host "📂 Menyiapkan direktori storage & file penanda (.gitkeep)..." -ForegroundColor Yellow

$storageDirs = @(
    "storage\app\public",
    "storage\app\visitors",
    "storage\app\packages",
    "storage\app\security-attendances",
    "storage\app\leave-requests",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs"
)

foreach ($sDir in $storageDirs) {
    $target = Join-Path $DeployDir $sDir
    if (-not (Test-Path $target)) {
        New-Item -ItemType Directory -Path $target -Force | Out-Null
    }
    $keep = Join-Path $target ".gitkeep"
    if (-not (Test-Path $keep)) {
        New-Item -ItemType File -Path $keep -Force | Out-Null
    }
}

# ------------------------------------------------------------------------------
# 7. Template .env Khusus Produksi (cPanel Shared Hosting)
# ------------------------------------------------------------------------------
Write-Host "📝 Membuat .env.production.example..." -ForegroundColor Yellow

$envContent = @'
# =============================================================================
# Villa Amabel Integrated System — Template .env untuk cPanel Shared Hosting
#
# Salin berkas ini menjadi '.env' di root hosting Anda, lalu sesuaikan isinya.
# APP_KEY akan dibuat otomatis oleh cpanel_setup.php jika masih kosong.
# =============================================================================

APP_NAME="Villa Amabel Integrated System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://amabel.web.id

APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

# -----------------------------------------------------------------------------
# Database MySQL (Diisi dari cPanel > MySQL Databases)
# -----------------------------------------------------------------------------
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=namauser_amabel
DB_USERNAME=namauser_amabel
DB_PASSWORD=

# -----------------------------------------------------------------------------
# Session, Cache & Queue (Memakai Database agar aman di Shared Hosting)
# -----------------------------------------------------------------------------
SESSION_DRIVER=database
SESSION_LIFETIME=10080
SESSION_ENCRYPT=false

CACHE_STORE=database
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

# -----------------------------------------------------------------------------
# Pengaturan Komplek & Privasi Data
# -----------------------------------------------------------------------------
GUESTBOOK_COMPLEX_NAME="Villa Amabel"
GUESTBOOK_PHOTO_RETENTION_DAYS=90

# -----------------------------------------------------------------------------
# Notifikasi Telegram Bot (Opsional)
# -----------------------------------------------------------------------------
TELEGRAM_ENABLED=false
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
TELEGRAM_THREAD_ID=

# -----------------------------------------------------------------------------
# Konfigurasi Email SMTP (Diisi dari cPanel > Email Accounts)
# -----------------------------------------------------------------------------
MAIL_MAILER=smtp
MAIL_HOST=mail.amabel.web.id
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@amabel.web.id"
MAIL_FROM_NAME="${APP_NAME}"
'@

Set-Content -Path (Join-Path $DeployDir ".env.production.example") -Value $envContent -Encoding UTF8

# ------------------------------------------------------------------------------
# 8. Script Pembantu Setup Otomatis di cPanel (cpanel_setup.php)
# ------------------------------------------------------------------------------
Write-Host "🛠️ Membuat script cPanel Automatic Setup (cpanel_setup.php)..." -ForegroundColor Yellow

$setupPhpContent = @'
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
    echo "1. Memeriksa APP_KEY & APP_URL...\n";
    $envPath = $basePath . '/.env';
    $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
    
    if (str_contains($envContent, 'APP_URL=http://localhost')) {
        $envContent = str_replace('APP_URL=http://localhost', 'APP_URL=https://amabel.web.id', $envContent);
        file_put_contents($envPath, $envContent);
        echo "   ✅ APP_URL di .env otomatis disesuaikan ke https://amabel.web.id\n";
    }

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

    echo "\n6. Mengoptimasi cache produksi (Config, Route, View, Filament)...\n";
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
'@

Set-Content -Path (Join-Path $DeployDir "public\cpanel_setup.php") -Value $setupPhpContent -Encoding UTF8

# ------------------------------------------------------------------------------
# 9. Berkas .htaccess di Root Deploy
# ------------------------------------------------------------------------------
Write-Host "📄 Menyiapkan .htaccess di root deploy..." -ForegroundColor Yellow

$htaccessContent = @'
<IfModule mod_rewrite.c>
    Options +FollowSymLinks -Indexes
    RewriteEngine On

    # Arahkan semua permintaan ke direktori public/
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>

# Blokir akses ke berkas sensitif jika root aplikasi terlayani langsung
<FilesMatch "^\.(env|git|editorconfig|json|lock|yml|yaml)|artisan$">
    Require all denied
</FilesMatch>
'@

Set-Content -Path (Join-Path $DeployDir ".htaccess") -Value $htaccessContent -Encoding UTF8

# ------------------------------------------------------------------------------
# 10. Kompresi ke deploy.zip
# ------------------------------------------------------------------------------
Write-Host "📦 Mengompresi folder deploy ke $ZipFile..." -ForegroundColor Cyan
Compress-Archive -Path "$DeployDir\*" -DestinationPath $ZipFile -Force

$zipSize = (Get-Item $ZipFile).Length / 1MB
Write-Host ("🎉 Berhasil membuat {0} ({1:N2} MB)." -f $ZipFile, $zipSize) -ForegroundColor Green

Write-Host ""
Write-Host "==========================================================================" -ForegroundColor Cyan
Write-Host "✅ PROSES EXPORT DEPLOYMENT VILLA AMABEL SELESAI" -ForegroundColor Green
Write-Host "==========================================================================" -ForegroundColor Cyan
Write-Host "Panduan Deployment ke cPanel Shared Hosting:"
Write-Host "1. Unggah 'deploy.zip' ke cPanel File Manager (di folder domain/public_html)."
Write-Host "2. Ekstrak file zip tersebut di cPanel File Manager."
Write-Host "3. Salin '.env.production.example' menjadi '.env', lalu isi kredensial MySQL"
Write-Host "   (DB_DATABASE, DB_USERNAME, DB_PASSWORD) dari cPanel > MySQL Databases."
Write-Host "4. Buka di browser: https://domain-anda.com/cpanel_setup.php"
Write-Host "   Script akan otomatis memigrasi database, seeding akun default, melakukan"
Write-Host "   optimasi cache produksi, dan menghapus dirinya sendiri."
Write-Host "5. Login ke portal internal dan segera ganti password akun default."
Write-Host "==========================================================================" -ForegroundColor Cyan
