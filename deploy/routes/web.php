<?php

use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\PackageDropOffController;
use App\Http\Controllers\ReceiptPhotoController;
use App\Http\Controllers\VisitorCheckInController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik — Landing Page, Form Tamu & Paket
|--------------------------------------------------------------------------
*/
Route::view('/', 'guestbook.home')->name('guestbook.home');

Route::get('/tamu', [VisitorCheckInController::class, 'create'])->name('visitors.create');
Route::get('/paket', [PackageDropOffController::class, 'create'])->name('packages.create');

Route::middleware('throttle:guestbook-submit')->group(function () {
    Route::post('/tamu', [VisitorCheckInController::class, 'store'])->name('visitors.store');
    Route::post('/paket', [PackageDropOffController::class, 'store'])->name('packages.store');
});

// Kartu bukti yang ditunjukkan tamu / kurir ke satpam
Route::view('/selesai', 'guestbook.done')->name('guestbook.done');
Route::get('/selesai/foto/{field}', ReceiptPhotoController::class)->name('guestbook.receipt-photo');

/*
|--------------------------------------------------------------------------
| Foto KTP / selfie / paket / presensi
|--------------------------------------------------------------------------
*/
Route::get('/internal/media/{type}/{record}/{field}', MediaController::class)
    ->middleware(['auth'])
    ->name('admin.media');
