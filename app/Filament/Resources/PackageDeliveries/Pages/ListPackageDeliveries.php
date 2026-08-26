<?php

namespace App\Filament\Resources\PackageDeliveries\Pages;

use App\Filament\Exports\PackageDeliveryExporter;
use App\Filament\Resources\PackageDeliveries\PackageDeliveryResource;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListPackageDeliveries extends ListRecords
{
    protected static string $resource = PackageDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Export CSV / Excel')
                ->exporter(PackageDeliveryExporter::class),
        ];
    }
}
