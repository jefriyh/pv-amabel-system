<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

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
        // Kompatibilitas key length MySQL / MariaDB shared hosting (menghindari error 1071)
        Schema::defaultStringLength(191);

        // Form tamu & paket terbuka tanpa login, jadi dibatasi per IP supaya tidak bisa dispam.
        // Angkanya sengaja longgar: satu keluarga tamu bisa mengisi beberapa form berurutan
        // dari satu jaringan wifi pos satpam.
        RateLimiter::for('guestbook-submit', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by($request->ip()),
        ]);

        // Auto-ensure schema columns exist on runtime
        try {
            if (Schema::hasTable('security_attendances')) {
                Schema::table('security_attendances', function (Blueprint $table) {
                    if (! Schema::hasColumn('security_attendances', 'previous_security_id')) {
                        $table->foreignId('previous_security_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
                    }
                    if (! Schema::hasColumn('security_attendances', 'start_time')) {
                        $table->time('start_time')->nullable()->after('attendance_time');
                    }
                    if (! Schema::hasColumn('security_attendances', 'end_time')) {
                        $table->time('end_time')->nullable()->after('start_time');
                    }
                });
            }

            if (Schema::hasTable('leave_requests')) {
                Schema::table('leave_requests', function (Blueprint $table) {
                    if (! Schema::hasColumn('leave_requests', 'selected_dates')) {
                        $table->json('selected_dates')->nullable()->after('type');
                    }
                });
            }
        } catch (Throwable $e) {
            // Ignore during setup/console commands before database is initialized
        }

        // Daftarkan path lokal resources/svg untuk Blade Icons & Heroicons agar aman di shared hosting
        $this->callAfterResolving(\BladeUI\Icons\Factory::class, function (\BladeUI\Icons\Factory $factory) {
            $paths = array_values(array_filter([
                base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg'),
                resource_path('svg/heroicons'),
                resource_path('svg'),
                public_path('vendor/blade-heroicons'),
            ], 'is_dir'));

            if (! empty($paths)) {
                try {
                    $factory->add('heroicons', [
                        'paths' => $paths,
                        'prefix' => 'heroicon',
                        'fallback' => 'fallback',
                    ]);
                } catch (\Throwable $e) {
                    // Set sudah terdaftar oleh BladeHeroiconsServiceProvider dengan konfigurasi config/blade-heroicons.php
                }
            }
        });
    }
}
