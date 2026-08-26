<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Driver GD sudah cukup untuk foto JPEG dari kamera HP dan tersedia di image
        // Docker kita; Imagick tidak dipasang supaya image tetap ramping.
        $this->app->singleton(ImageManager::class, fn () => new ImageManager(new Driver));
    }

    public function boot(): void
    {
        // Form tamu & paket terbuka tanpa login, jadi dibatasi per IP supaya tidak bisa dispam.
        // Angkanya sengaja longgar: satu keluarga tamu bisa mengisi beberapa form berurutan
        // dari satu jaringan wifi pos satpam.
        RateLimiter::for('guestbook-submit', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by($request->ip()),
        ]);
    }
}
