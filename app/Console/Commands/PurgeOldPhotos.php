<?php

namespace App\Console\Commands;

use App\Models\PackageDelivery;
use App\Models\Visitor;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class PurgeOldPhotos extends Command
{
    protected $signature = 'guestbook:purge-photos
                            {--days= : Umur foto dalam hari (default dari config guestbook.photo_retention_days)}
                            {--dry-run : Hanya tampilkan yang akan dihapus, tanpa menghapus}';

    protected $description = 'Hapus foto KTP/selfie/paket yang sudah melewati masa retensi (baris rekapnya tetap disimpan)';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('guestbook.photo_retention_days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->components->info(sprintf(
            '%s foto yang dibuat sebelum %s (retensi %d hari).',
            $dryRun ? 'Mendata' : 'Menghapus',
            $cutoff->format('d M Y H:i'),
            $days,
        ));

        $files = 0;
        $rows = 0;

        foreach ([Visitor::class, PackageDelivery::class] as $model) {
            /** @var class-string<Visitor|PackageDelivery> $model */
            $query = $model::query()
                ->where('created_at', '<', $cutoff)
                ->whereNull('photos_purged_at')
                ->where(function (Builder $q) use ($model) {
                    foreach (array_keys($model::photoFields()) as $field) {
                        $q->orWhereNotNull($field);
                    }
                });

            $query->chunkById(200, function ($entries) use (&$files, &$rows, $dryRun) {
                foreach ($entries as $entry) {
                    $rows++;

                    if ($dryRun) {
                        $files += collect(array_keys($entry::photoFields()))
                            ->filter(fn (string $field) => filled($entry->{$field}))
                            ->count();

                        $this->line("  [dry-run] {$entry->getKey()} — {$entry->created_at->format('d M Y')}");

                        continue;
                    }

                    $files += $entry->purgePhotos();
                }
            });
        }

        $this->components->info(
            $dryRun
                ? "Akan dihapus: {$files} file dari {$rows} entri."
                : "Selesai: {$files} file dihapus dari {$rows} entri."
        );

        return self::SUCCESS;
    }
}
