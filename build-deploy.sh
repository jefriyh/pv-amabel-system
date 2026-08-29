#!/bin/bash

# ==============================================================================
# Villa Amabel — Integrated System
# Script Build & Export Deployment cPanel Shared Hosting
#
# CARA MENJALANKAN DI DOCKER:
#
# Langkah 1 (Kompilasi Frontend):
#   docker compose run --rm node npm run build
#
# Langkah 2 (Export Deploy ZIP):
#   docker compose exec app bash build-deploy.sh [--fresh]
#
# Hasilnya: File 'deploy.zip' di root proyek siap diunggah ke cPanel File Manager.
# ==============================================================================

set -euo pipefail

# Deteksi direktori kerja (di dalam container /var/www/html atau di host)
if [ -f "/var/www/html/artisan" ]; then
    WORKDIR="/var/www/html"
elif [ -f "/app/artisan" ]; then
    WORKDIR="/app"
else
    WORKDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
fi

DEPLOY_DIR="$WORKDIR/deploy"
ZIP_FILE="$WORKDIR/deploy.zip"

IS_FRESH=false
for arg in "$@"; do
    if [ "$arg" = "--fresh" ]; then
        IS_FRESH=true
    fi
done

echo "=========================================================================="
echo "🚀 BUILD & EXPORT cPANEL — VILLA AMABEL INTEGRATED SYSTEM"
echo "=========================================================================="
if [ "$IS_FRESH" = true ]; then
    echo "🧹 Mode --fresh aktif: Membersihkan cache & build lama..."
fi

# ------------------------------------------------------------------------------
# 0. Pemeriksaan Awal
# ------------------------------------------------------------------------------
echo "🔎 Memeriksa prasyarat build..."

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
case "$SCRIPT_DIR/" in
    "$DEPLOY_DIR"/*)
        echo "❌ ERROR: Script ini dijalankan dari dalam folder deploy/."
        echo "   Jalankan script yang berada di root proyek:"
        echo "   docker compose exec app bash build-deploy.sh"
        exit 1
        ;;
esac

if [ ! -f "$WORKDIR/artisan" ]; then
    echo "❌ ERROR: $WORKDIR/artisan tidak ditemukan. Pastikan script dijalankan dari container app."
    exit 1
fi

for tool in php composer; do
    if ! command -v "$tool" >/dev/null 2>&1; then
        echo "❌ ERROR: '$tool' tidak tersedia di container ini."
        exit 1
    fi
done

echo "   PHP     : $(php -r 'echo PHP_VERSION;')"
echo "   Composer: $(composer --version 2>/dev/null | cut -d' ' -f3 || echo '-')"

# ------------------------------------------------------------------------------
# 1. Bersihkan Folder Deploy & File ZIP Lama
# ------------------------------------------------------------------------------
echo "🧹 Membersihkan output deploy sebelumnya..."
if [ -d "$DEPLOY_DIR" ]; then
    chmod -R 777 "$DEPLOY_DIR" 2>/dev/null || true
    rm -rf "$DEPLOY_DIR" 2>/dev/null || (find "$DEPLOY_DIR" -mindepth 1 -delete 2>/dev/null) || true
fi
mkdir -p "$DEPLOY_DIR"
rm -f "$ZIP_FILE"

# ------------------------------------------------------------------------------
# 2. Periksa / Build Asset Frontend Production (Vite / Tailwind)
# ------------------------------------------------------------------------------
echo "📦 Memeriksa asset frontend production..."

if command -v npm >/dev/null 2>&1; then
    echo "   Menjalankan npm run build..."
    if [ "$IS_FRESH" = true ]; then
        rm -rf "$WORKDIR/public/build"
    fi
    rm -f "$WORKDIR/public/hot"
    (cd "$WORKDIR" && NODE_ENV=production npm run build)
fi

if [ ! -f "$WORKDIR/public/build/manifest.json" ]; then
    echo ""
    echo "❌ ERROR: public/build/manifest.json tidak ditemukan!"
    echo "   Harap jalankan perintah build frontend dari container node terlebih dahulu:"
    echo ""
    echo "       docker compose run --rm node npm run build"
    echo ""
    echo "   Lalu jalankan ulang script ini."
    exit 1
fi
echo "✅ Asset frontend production siap."

# ------------------------------------------------------------------------------
# 3. Salin Berkas Aplikasi ke Folder Deploy
# ------------------------------------------------------------------------------
echo "📋 Menyalin berkas aplikasi ke folder deploy..."
cp -R "$WORKDIR/app" "$DEPLOY_DIR/"
cp -R "$WORKDIR/bootstrap" "$DEPLOY_DIR/"
cp -R "$WORKDIR/config" "$DEPLOY_DIR/"
cp -R "$WORKDIR/database" "$DEPLOY_DIR/"
cp -R "$WORKDIR/public" "$DEPLOY_DIR/"
cp -R "$WORKDIR/resources" "$DEPLOY_DIR/"
cp -R "$WORKDIR/routes" "$DEPLOY_DIR/"
cp "$WORKDIR/artisan" "$DEPLOY_DIR/"
cp "$WORKDIR/composer.json" "$DEPLOY_DIR/"

if [ -f "$WORKDIR/composer.lock" ]; then
    cp "$WORKDIR/composer.lock" "$DEPLOY_DIR/"
fi

# Bersihkan symlink & temporary files dari folder deploy
rm -rf "$DEPLOY_DIR/public/storage"
rm -f "$DEPLOY_DIR/public/hot"
rm -f "$DEPLOY_DIR/.env"

# ------------------------------------------------------------------------------
# 4. Buang Cache Bootstrap Milik Lingkungan Pengembangan
# ------------------------------------------------------------------------------
echo "🧽 Membersihkan cache bootstrap bawaan dev..."
rm -f "$DEPLOY_DIR"/bootstrap/cache/*.php
rm -rf "$DEPLOY_DIR/bootstrap/ssr"

# ------------------------------------------------------------------------------
# 5. Install Dependensi Composer Production
# ------------------------------------------------------------------------------
echo "⚙️ Menginstall dependensi Composer Production di folder deploy..."

if [ -d "$WORKDIR/vendor" ]; then
    echo "   Menyalin vendor lokal sebagai basis awal..."
    cp -R "$WORKDIR/vendor" "$DEPLOY_DIR/"
fi

composer_install() {
    composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --no-progress \
        --optimize-autoloader \
        --working-dir="$DEPLOY_DIR"
}

COMPOSER_ATTEMPT=1
COMPOSER_MAX_ATTEMPTS=3

until composer_install; do
    if [ "$COMPOSER_ATTEMPT" -ge "$COMPOSER_MAX_ATTEMPTS" ]; then
        echo ""
        echo "❌ ERROR: composer install gagal setelah $COMPOSER_MAX_ATTEMPTS percobaan."
        exit 1
    fi

    COMPOSER_WAIT=$((COMPOSER_ATTEMPT * 15))
    echo "   ⚠️  Percobaan $COMPOSER_ATTEMPT gagal. Mengulang dalam ${COMPOSER_WAIT} detik..."
    sleep "$COMPOSER_WAIT"
    COMPOSER_ATTEMPT=$((COMPOSER_ATTEMPT + 1))
done

# ------------------------------------------------------------------------------
# 5b. Pastikan Seluruh SVG Heroicons Tersalin Lengkap ke resources/svg/heroicons
# ------------------------------------------------------------------------------
echo "🎨 Memastikan seluruh ikon Heroicons SVG tersalin lengkap ke resources/svg/heroicons..."
mkdir -p "$DEPLOY_DIR/resources/svg/heroicons"
if [ -d "$DEPLOY_DIR/vendor/blade-ui-kit/blade-heroicons/resources/svg" ]; then
    cp -f "$DEPLOY_DIR/vendor/blade-ui-kit/blade-heroicons/resources/svg/"*.svg "$DEPLOY_DIR/resources/svg/heroicons/" 2>/dev/null || true
fi
if [ -d "$WORKDIR/vendor/blade-ui-kit/blade-heroicons/resources/svg" ]; then
    cp -f "$WORKDIR/vendor/blade-ui-kit/blade-heroicons/resources/svg/"*.svg "$DEPLOY_DIR/resources/svg/heroicons/" 2>/dev/null || true
fi
if [ -d "$WORKDIR/resources/svg/heroicons" ]; then
    cp -f "$WORKDIR/resources/svg/heroicons/"*.svg "$DEPLOY_DIR/resources/svg/heroicons/" 2>/dev/null || true
fi

# ------------------------------------------------------------------------------
# 6. Siapkan Struktur Direktori Storage yang Lengkap
# ------------------------------------------------------------------------------
echo "📂 Menyiapkan direktori storage & file penanda (.gitkeep)..."
for d in \
    storage/app/public \
    storage/app/visitors \
    storage/app/packages \
    storage/app/security-attendances \
    storage/app/leave-requests \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs
do
    mkdir -p "$DEPLOY_DIR/$d"
    touch "$DEPLOY_DIR/$d/.gitkeep"
done

chmod -R 775 "$DEPLOY_DIR/storage" "$DEPLOY_DIR/bootstrap/cache" 2>/dev/null || true

# ------------------------------------------------------------------------------
# 7. Template .env Khusus Produksi (cPanel Shared Hosting)
# ------------------------------------------------------------------------------
echo "📝 Membuat .env.production.example..."
cat << 'ENVEOF' > "$DEPLOY_DIR/.env.production.example"
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
ENVEOF

# ------------------------------------------------------------------------------
# 8. Script Pembantu Setup Otomatis di cPanel (cpanel_setup.php)
# ------------------------------------------------------------------------------
echo "🛠️ Membuat script cPanel Automatic Setup (cpanel_setup.php)..."
cat << 'EOF' > "$DEPLOY_DIR/public/cpanel_setup.php"
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
EOF

# ------------------------------------------------------------------------------
# 9. Berkas .htaccess untuk Root Deployment
# ------------------------------------------------------------------------------
echo "📄 Menyiapkan .htaccess di root deploy..."
cat << 'EOF' > "$DEPLOY_DIR/.htaccess"
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
EOF

# ------------------------------------------------------------------------------
# 10. Kompresi ke deploy.zip
# ------------------------------------------------------------------------------
if command -v zip >/dev/null 2>&1; then
    echo "📦 Mengompresi folder deploy ke $ZIP_FILE..."
    (cd "$DEPLOY_DIR" && zip -rq "$ZIP_FILE" .)
    echo "🎉 Berhasil membuat $ZIP_FILE ($(du -h "$ZIP_FILE" | cut -f1 2>/dev/null || echo 'OK'))."
else
    echo "📦 Mengompresi menggunakan PHP ZipArchive ke $ZIP_FILE..."
    php -r '
        $zipPath = $argv[1];
        $sourceDir = $argv[2];
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            exit("Gagal membuat zip\n");
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                $relativePath = str_replace("\\", "/", $relativePath);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    ' "$ZIP_FILE" "$DEPLOY_DIR"
    echo "🎉 Berhasil membuat $ZIP_FILE."
fi

echo ""
echo "=========================================================================="
echo "✅ PROSES EXPORT DEPLOYMENT VILLA AMABEL SELESAI"
echo "=========================================================================="
echo "Panduan Deployment ke cPanel Shared Hosting:"
echo "1. Unggah '$ZIP_FILE' ke cPanel File Manager (di public_html atau root domain)."
echo "2. Ekstrak file zip tersebut di cPanel File Manager."
echo "3. Salin '.env.production.example' menjadi '.env', lalu isi kredensial MySQL"
echo "   (DB_DATABASE, DB_USERNAME, DB_PASSWORD) dari cPanel > MySQL Databases."
echo "4. Buka di browser: https://domain-anda.com/cpanel_setup.php"
echo "   Script akan otomatis memigrasi database, membuat akun default, melakukan"
echo "   optimasi cache produksi, dan menghapus dirinya sendiri."
echo "5. Login ke portal internal dan segera ganti password akun default."
echo "=========================================================================="
