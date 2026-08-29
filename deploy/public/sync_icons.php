<?php
/**
 * Villa Amabel - Synchronize All Heroicons to Resources
 * Buka file ini di browser: https://amabel.web.id/sync_icons.php
 */

$basePath = dirname(__DIR__);
$vendorSvgDir = $basePath . '/vendor/blade-ui-kit/blade-heroicons/resources/svg';
$targetSvgDir = $basePath . '/resources/svg/heroicons';

if (!is_dir($targetSvgDir)) {
    mkdir($targetSvgDir, 0755, true);
}

$copied = 0;
if (is_dir($vendorSvgDir)) {
    $files = glob($vendorSvgDir . '/*.svg');
    foreach ($files as $file) {
        $filename = basename($file);
        if (copy($file, $targetSvgDir . '/' . $filename)) {
            $copied++;
        }
    }
}

// Bersihkan cache icon
foreach (glob($basePath . '/bootstrap/cache/*.php') as $cacheFile) {
    @unlink($cacheFile);
}
foreach (glob($basePath . '/storage/framework/views/*.php') as $viewCache) {
    @unlink($viewCache);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinkronisasi Icon - Villa Amabel</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 2rem; }
        .card { max-width: 600px; margin: 0 auto; background: #1e293b; padding: 2rem; border-radius: 12px; border: 1px solid #334155; }
        h1 { color: #38bdf8; margin-top: 0; font-size: 1.5rem; }
        .success { color: #4ade80; font-weight: bold; }
        .btn { display: inline-block; background: #0284c7; color: white; padding: 10px 18px; border-radius: 8px; text-decoration: none; margin-top: 15px; font-weight: 600; }
        .btn:hover { background: #0369a1; }
    </style>
</head>
<body>
    <div class="card">
        <h1>✅ Sinkronisasi Icon Selesai</h1>
        <p class="success">Berhasil menyalin <strong><?= $copied ?></strong> file icon Heroicons ke <code>resources/svg/heroicons/</code>.</p>
        <p>Cache Blade Icons & View Cache telah dibersihkan secara otomatis.</p>
        <hr style="border-color: #334155; margin: 1.5rem 0;">
        <a href="/internal" class="btn">🚀 Buka Dashboard Internal</a>
    </div>
</body>
</html>
