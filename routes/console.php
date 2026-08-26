<?php

use App\Console\Commands\PurgeOldPhotos;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tugas terjadwal
|--------------------------------------------------------------------------
|
| Dijalankan container "scheduler" (php artisan schedule:work).
|
*/

// Foto KTP & selfie adalah data pribadi — dihapus otomatis setelah masa retensi.
// Jam 02:15 dipilih karena jauh dari jam sibuk tamu.
Schedule::command(PurgeOldPhotos::class)
    ->dailyAt('02:15')
    ->timezone(config('app.timezone'))
    ->onOneServer()
    ->withoutOverlapping();

// Rekap hasil export yang sudah diunduh tidak perlu menumpuk di storage.
Schedule::command('model:prune', ['--model' => [Export::class]])
    ->daily()
    ->timezone(config('app.timezone'));
