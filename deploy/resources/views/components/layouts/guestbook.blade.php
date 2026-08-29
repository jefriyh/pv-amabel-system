<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Buku Tamu' }} - {{ config('guestbook.complex_name') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('favicon.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 text-slate-900 antialiased">
    <div class="mx-auto flex min-h-screen w-full max-w-lg flex-col px-4 py-6">
        <header class="mb-6 text-center">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Buku Tamu</p>
            <h1 class="mt-1 text-xl font-bold text-slate-900">{{ config('guestbook.complex_name') }}</h1>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="mt-8 text-center text-xs leading-relaxed text-slate-500">
            Data dan foto yang Anda kirim hanya digunakan untuk keamanan lingkungan komplek,
            dapat dilihat oleh pengurus, dan foto dihapus otomatis setelah
            {{ config('guestbook.photo_retention_days') }} hari.
        </footer>
    </div>
</body>
</html>
