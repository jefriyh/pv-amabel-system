<?php

namespace App\Filament\Resources\Visitors\Schemas;

use App\Http\Controllers\Admin\MediaController;
use App\Models\Visitor;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Tamu')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nama lengkap')->weight('bold'),
                        TextEntry::make('created_at')->label('Waktu masuk')->dateTime('l, d F Y H:i'),
                        TextEntry::make('phone')->label('Nomor HP')->placeholder('Tidak diisi')->copyable(),
                        TextEntry::make('host_name')->label('Menemui')->placeholder('Tidak diisi'),
                        TextEntry::make('purpose')->label('Keperluan')->columnSpanFull(),
                    ]),

                Section::make('Foto')
                    ->columns(2)
                    ->description('Foto hanya bisa dilihat dari halaman ini dan tidak pernah dikirim ke grup Telegram.')
                    ->schema([
                        ImageEntry::make('ktp_path')
                            ->label('Foto KTP')
                            ->state(fn (Visitor $record) => MediaController::urlFor($record, 'ktp_path'))
                            ->placeholder('Foto sudah dihapus sesuai masa retensi')
                            ->imageHeight(280)
                            ->extraImgAttributes(['class' => 'rounded-lg object-contain']),

                        ImageEntry::make('selfie_path')
                            ->label('Foto selfie')
                            ->state(fn (Visitor $record) => MediaController::urlFor($record, 'selfie_path'))
                            ->placeholder('Foto sudah dihapus sesuai masa retensi')
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
