<?php

namespace App\Filament\Resources\PackageDeliveries\Schemas;

use App\Http\Controllers\Admin\MediaController;
use App\Models\PackageDelivery;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PackageDeliveryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Paket')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('courier_name')->label('Nama kurir')->weight('bold'),
                        TextEntry::make('courier_company')->label('Ekspedisi')->badge(),
                        TextEntry::make('created_at')->label('Waktu drop-off')->dateTime('l, d F Y H:i'),
                        TextEntry::make('tracking_number')->label('Nomor resi')->placeholder('Tidak diisi')->copyable(),
                        TextEntry::make('recipient_note')->label('Paket untuk')->placeholder('Tidak diisi')->columnSpanFull(),
                    ]),

                Section::make('Foto')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('photo_path')
                            ->label('Foto paket di kotak')
                            ->state(fn (PackageDelivery $record) => MediaController::urlFor($record, 'photo_path'))
                            ->placeholder('Foto sudah dihapus sesuai masa retensi')
                            ->imageHeight(280)
                            ->extraImgAttributes(['class' => 'rounded-lg object-contain']),

                        ImageEntry::make('selfie_path')
                            ->label('Foto kurir')
                            ->state(fn (PackageDelivery $record) => MediaController::urlFor($record, 'selfie_path'))
                            ->placeholder('Tidak ada foto')
                            ->imageHeight(280)
                            ->extraImgAttributes(['class' => 'rounded-lg object-contain']),
                    ]),

                Section::make('Jejak Teknis')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('id')->label('ID entri')->copyable(),
                        TextEntry::make('ip_address')->label('Alamat IP')->placeholder('—'),
                        TextEntry::make('user_agent')->label('Perangkat')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('photos_purged_at')
                            ->label('Foto dihapus pada')
                            ->dateTime('d F Y H:i')
                            ->placeholder('Belum dihapus'),
                    ]),
            ]);
    }
}
