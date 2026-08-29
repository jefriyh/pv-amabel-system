<?php

namespace App\Filament\Resources\Visitors\Schemas;

use App\Models\Visitor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Tamu')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(120),

                        TextInput::make('phone')
                            ->label('Nomor HP (Opsional)')
                            ->tel()
                            ->maxLength(30),

                        TextInput::make('host_name')
                            ->label('Menemui Siapa / No Rumah')
                            ->maxLength(120),

                        Select::make('status')
                            ->label('Status Kunjungan')
                            ->options(Visitor::getStatusLabels())
                            ->required(),

                        Textarea::make('purpose')
                            ->label('Keperluan')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('approval_notes')
                            ->label('Catatan Approval')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
