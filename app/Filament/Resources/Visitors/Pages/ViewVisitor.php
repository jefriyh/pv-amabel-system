<?php

namespace App\Filament\Resources\Visitors\Pages;

use App\Filament\Resources\Visitors\VisitorResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVisitor extends ViewRecord
{
    protected static string $resource = VisitorResource::class;

    public function getTitle(): string
    {
        return 'Tamu: '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        // Sengaja kosong: entri buku tamu tidak boleh disunting dari dashboard.
        return [];
    }
}
