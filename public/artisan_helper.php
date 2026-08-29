<?php
/**
 * Artisan Web Runner — Villa Amabel Integrated System
 * 
 * Jalankan perintah Artisan langsung melalui browser tanpa Terminal / SSH:
 * Akses: https://domain-anda.com/artisan_helper.php
 * 
 * PENTING: Hapus file ini setelah selesai melakukan konfigurasi!
 */

define('LARAVEL_START', microtime(true));

$basePath = dirname(__DIR__);

// Hapus cache lama (termasuk blade-icons.php)
foreach (glob($basePath . '/bootstrap/cache/*.php') as $cacheFile) {
    @unlink($cacheFile);
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Bootstrap kernel Laravel agar config & database ter-load
$kernel->bootstrap();

$output = '';
$action = $_GET['action'] ?? '';

if ($action) {
    ob_start();
    try {
        switch ($action) {
            case 'key_generate':
                $kernel->call('key:generate', ['--force' => true]);
                echo $kernel->output();
                break;

            case 'storage_link':
                $targetPath = storage_path('app/public');
                $linkPath = public_path('storage');

                if (!is_dir($targetPath)) {
                    @mkdir($targetPath, 0755, true);
                }

                if (is_link($linkPath)) {
                    @unlink($linkPath);
                } elseif (is_dir($linkPath)) {
                    @rmdir($linkPath);
                }

                try {
                    $kernel->call('storage:link', ['--force' => true]);
                    echo $kernel->output();
                } catch (\Throwable $e) {
                    if (@symlink($targetPath, $linkPath)) {
                        echo "✅ Symlink storage berhasil dibuat secara manual via PHP symlink()!\n";
                    } else {
                        echo "⚠️ Gagal membuat symlink: " . $e->getMessage() . "\n";
                        echo "Catatan: Jika shared hosting melarang symlink, fungsi penyimpanan tetap berjalan di storage/app/.\n";
                    }
                }
                break;

            case 'migrate':
                $kernel->call('migrate', ['--force' => true]);
                echo $kernel->output();
                break;

            case 'seed':
                $kernel->call('db:seed', ['--class' => 'Database\\Seeders\\UserSeeder', '--force' => true]);
                echo $kernel->output();
                break;

            case 'optimize_clear':
                $kernel->call('optimize:clear');
                echo $kernel->output();
                try {
                    $kernel->call('icons:clear');
                    echo $kernel->output();
                    $kernel->call('view:clear');
                    echo $kernel->output();
                } catch (\Throwable $ex) {}
                break;

            case 'optimize_cache':
                $kernel->call('config:cache');
                echo $kernel->output();
                $kernel->call('route:cache');
                echo $kernel->output();
                $kernel->call('view:cache');
                echo $kernel->output();
                break;

            case 'delete_self':
                @unlink(__FILE__);
                die("<h3>🔒 File artisan_helper.php telah berhasil dihapus demi keamanan.</h3><p><a href='/admin'>Kembali ke Admin Portal</a></p>");

            default:
                echo "Aksi tidak dikenal.";
        }
    } catch (\Throwable $e) {
        echo "❌ Terjadi Error:\n" . $e->getMessage() . "\n\nStack Trace:\n" . $e->getTraceAsString();
    }
    $output = ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisan Web Runner — Villa Amabel</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 2rem 1rem; }
        .container { max-width: 760px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 2rem; border: 1px solid #334155; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        h1 { font-size: 1.5rem; color: #38bdf8; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
        p.subtitle { color: #94a3b8; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; text-decoration: none; transition: all 0.2s; border: none; cursor: pointer; text-align: center; }
        .btn-primary { background: #0284c7; color: white; }
        .btn-primary:hover { background: #0369a1; }
        .btn-success { background: #16a34a; color: white; }
        .btn-success:hover { background: #15803d; }
        .btn-warning { background: #d97706; color: white; }
        .btn-warning:hover { background: #b45309; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-purple { background: #7c3aed; color: white; }
        .btn-purple:hover { background: #6d28d9; }
        .output-box { background: #090d16; border: 1px solid #334155; border-radius: 8px; padding: 1rem; margin-top: 1.5rem; }
        .output-box h3 { font-size: 0.9rem; color: #94a3b8; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        pre { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.85rem; color: #4ade80; white-space: pre-wrap; word-break: break-all; }
        .warning-card { background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.4); border-radius: 8px; padding: 1rem; margin-top: 1.5rem; color: #fca5a5; font-size: 0.85rem; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ Artisan Web Runner (Tanpa SSH / Terminal)</h1>
        <p class="subtitle">Jalankan perintah maintenance Laravel langsung dari browser di cPanel Shared Hosting.</p>

        <div class="grid">
            <a href="?action=key_generate" class="btn btn-primary">🔑 1. Generate APP_KEY</a>
            <a href="?action=storage_link" class="btn btn-primary">🔗 2. Buat Storage Link</a>
            <a href="?action=migrate" class="btn btn-success">📦 3. Migrate Database</a>
            <a href="?action=seed" class="btn btn-purple">👥 4. Seed Akun Default</a>
            <a href="?action=optimize_clear" class="btn btn-warning">🧹 5. Clear All Cache & Icons</a>
            <a href="?action=optimize_cache" class="btn btn-warning">⚡ 6. Cache Production</a>
        </div>

        <?php if ($output): ?>
            <div class="output-box">
                <h3>Hasil Eksekusi:</h3>
                <pre><?= htmlspecialchars($output) ?></pre>
            </div>
        <?php endif; ?>

        <div class="warning-card">
            <strong>🔒 PENTING:</strong> Setelah selesai menjalankan perintah di atas dan aplikasi berjalan lancar, segera klik tombol di bawah untuk menghapus file ini demi keamanan hosting Anda:
            <div style="margin-top: 0.75rem;">
                <a href="?action=delete_self" onclick="return confirm('Apakah Anda yakin ingin menghapus file artisan_helper.php ini?')" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.8rem;">🗑️ Hapus File artisan_helper.php Sekarang</a>
            </div>
        </div>
    </div>
</body>
</html>
