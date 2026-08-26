<?php

namespace App\Filament\Exports;

use App\Models\Visitor;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class VisitorExporter extends Exporter
{
    protected static ?string $model = Visitor::class;

    /**
     * Foto tidak ikut diekspor sebagai file. Yang diekspor hanya tautan ke halaman
     * detail, supaya file rekap boleh beredar tanpa ikut menyebarkan foto KTP.
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('Waktu Masuk')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s')),

            ExportColumn::make('name')->label('Nama'),
            ExportColumn::make('phone')->label('Nomor HP'),
            ExportColumn::make('host_name')->label('Menemui'),
            ExportColumn::make('purpose')->label('Keperluan'),

            ExportColumn::make('detail_url')
                ->label('Tautan Detail')
                ->state(fn (Visitor $record) => route('filament.admin.resources.visitors.view', ['record' => $record])),

            ExportColumn::make('id')->label('ID Entri'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "Export data tamu selesai: {$export->successful_rows} baris berhasil diekspor.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} baris gagal.";
        }

        return $body;
    }
}
