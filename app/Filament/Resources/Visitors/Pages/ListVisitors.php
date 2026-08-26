<?php

namespace App\Filament\Resources\Visitors\Pages;

use App\Filament\Exports\VisitorExporter;
use App\Filament\Resources\Visitors\VisitorResource;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListVisitors extends ListRecords
{
    protected static string $resource = VisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Mengikuti filter tabel yang sedang aktif, jadi rekap per rentang tanggal
            // tinggal: pasang filter periode -> Export.
            ExportAction::make()
                ->label('Export CSV / Excel')
                ->exporter(VisitorExporter::class),
        ];
    }
}
