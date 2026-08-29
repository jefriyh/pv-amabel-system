<?php

namespace App\Filament\Resources\SecurityAttendances\Pages;

use App\Filament\Resources\SecurityAttendances\SecurityAttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSecurityAttendances extends ListRecords
{
    protected static string $resource = SecurityAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Presensi Sekarang')
                ->icon('heroicon-m-camera'),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();
        $user = auth()->user();

        if ($user && $user->isSecurity()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }
}
