<?php

namespace App\Filament\Resources\PackageDeliveries\Schemas;

use App\Models\PackageDelivery;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PackageDeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pengiriman Paket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('courier_name')
                            ->label('Nama Kurir')
                            ->required()
                            ->maxLength(120),

                        Select::make('courier_company')
                            ->label('Ekspedisi')
                            ->options(fn () => array_combine(config('guestbook.couriers'), config('guestbook.couriers')))
                            ->required(),

                        TextInput::make('recipient_note')
                            ->label('Untuk Siapa / No Rumah')
                            ->maxLength(160),

                        TextInput::make('tracking_number')
                            ->label('Nomor Resi')
                            ->maxLength(80),

                        Select::make('status')
                            ->label('Status Paket')
                            ->options(PackageDelivery::getStatusLabels())
                            ->required(),

                        TextInput::make('received_by')
                            ->label('Diterima Oleh (Penghuni/Keluarga)')
                            ->placeholder('Nama penerima paket...')
                            ->maxLength(120),

                        DateTimePicker::make('received_at')
                            ->label('Waktu Diterima')
                            ->native(false),
                    ]),
            ]);
    }
}
