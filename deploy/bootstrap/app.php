<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aplikasi berjalan di belakang nginx + reverse proxy (Cloudflare/Nginx Proxy Manager).
        // Tanpa ini Laravel menganggap request-nya http:// sehingga URL yang di-generate
        // jadi http — dan kamera HP tidak mau aktif karena bukan secure context.
        $middleware->trustProxies(at: '*');

        // Route foto memakai middleware "auth" biasa, sementara halaman login ada di
        // panel Filament. Tanpa ini admin yang sesinya habis mendapat 403 buntu.
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
