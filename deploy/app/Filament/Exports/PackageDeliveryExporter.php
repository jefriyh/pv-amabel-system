<?php

namespace App\Filament\Exports;

use App\Models\PackageDelivery;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PackageDeliveryExporter extends Exporter
{
    protected static ?string $model = PackageDelivery::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('Waktu Drop-off')
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s')),

            ExportColumn::make('courier_name')->label('Nama Kurir'),
            ExportColumn::make('courier_company')->label('Ekspedisi'),
            ExportColumn::make('recipient_note')->label('Penerima'),
            ExportColumn::make('tracking_number')->label('Nomor Resi'),

            ExportColumn::make('detail_url')
                ->label('Tautan Detail')
                ->state(fn (PackageDelivery $record) => route('filament.admin.resources.package-deliveries.view', ['record' => $record])),

            ExportColumn::make('id')->label('ID Entri'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "Export data paket selesai: {$export->successful_rows} baris berhasil diekspor.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " {$failedRowsCount} baris gagal.";
        }

        return $body;
    }
}
