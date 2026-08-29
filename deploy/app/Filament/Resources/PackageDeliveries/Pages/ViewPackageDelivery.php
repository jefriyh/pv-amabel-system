<?php

namespace App\Filament\Resources\PackageDeliveries\Pages;

use App\Filament\Resources\PackageDeliveries\PackageDeliveryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPackageDelivery extends ViewRecord
{
    protected static string $resource = PackageDeliveryResource::class;

    public function getTitle(): string
    {
        return 'Paket dari '.$this->record->courier_name;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
