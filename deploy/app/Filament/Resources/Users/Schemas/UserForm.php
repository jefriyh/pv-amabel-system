<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Kelola data akun pengguna. Pengurus dan Security dapat login menggunakan Email atau Nomor HP.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(120),

                        Select::make('role')
                            ->label('Peran / Role')
                            ->options(User::getRoleLabels())
                            ->default(User::ROLE_PENGURUS)
                            ->required()
                            ->reactive(),

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(fn ($get): bool => blank($get('phone')))
                            ->helperText('Dapat digunakan untuk login. Wajib jika Nomor HP tidak diisi.')
                            ->maxLength(120),

                        TextInput::make('phone')
                            ->label('Nomor HP / WhatsApp')
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->required(fn ($get): bool => blank($get('email')))
                            ->helperText('Dapat digunakan untuk login. Wajib jika Email tidak diisi.')
                            ->placeholder('08xxxxxxxxxx')
                            ->maxLength(30),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('Kosongkan jika tidak ingin mengubah password saat edit.'),

                        TextInput::make('annual_leave_quota')
                            ->label('Jatah Cuti Tahunan (Hari)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(365)
                            ->default(12)
                            ->required()
                            ->helperText('Jumlah jatah hari cuti tahunan (terutama untuk Security).'),

                        Toggle::make('is_active')
                            ->label('Status Akun Aktif')
                            ->default(true)
                            ->helperText('Jika nonaktif, pengguna tidak dapat login ke sistem.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
