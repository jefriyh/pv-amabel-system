<?php

namespace App\Filament\Resources\SecurityAttendances\Pages;

use App\Filament\Resources\SecurityAttendances\SecurityAttendanceResource;
use App\Models\SecurityAttendance;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewSecurityAttendance extends ViewRecord
{
    protected static string $resource = SecurityAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('share_whatsapp')
                ->label('Bagikan ke WhatsApp')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->modalHeading('Bagikan Ringkasan Presensi ke WhatsApp')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn (SecurityAttendance $record) => view('filament.components.attendance-share-modal', [
                    'record' => $record->loadMissing(['user', 'previousSecurity']),
                ])),
        ];
    }
}
