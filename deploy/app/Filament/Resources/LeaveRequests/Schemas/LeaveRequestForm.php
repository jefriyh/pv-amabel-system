<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSecurity = $user?->isSecurity();

        return $schema
            ->components([
                $isSecurity
                    ? Hidden::make('user_id')->default($user->id)
                    : Select::make('user_id')
                        ->label('Petugas Security')
                        ->options(User::where('role', User::ROLE_SECURITY)->pluck('name', 'id'))
                        ->default($user?->id)
                        ->required()
                        ->searchable()
                        ->columnSpanFull(),

                Select::make('type')
                    ->label('Jenis Pengajuan')
                    ->options(LeaveRequest::getTypeLabels())
                    ->default(LeaveRequest::TYPE_CUTI)
                    ->required()
                    ->live()
                    ->columnSpanFull(),

                ViewField::make('selected_dates')
                    ->label('Pilih Tanggal (Bisa Lebih Dari 1)')
                    ->view('filament.forms.components.cinema-date-picker')
                    ->default([now()->toDateString()])
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('attachment_path')
                    ->label('Dokumen / Lampiran Pendukung (Opsional)')
                    ->disk('local')
                    ->directory('leaves')
                    ->image()
                    ->maxSize(5120)
                    ->helperText('Opsional: Unggah foto surat dokter atau bukti pendukung jika ada.')
                    ->columnSpanFull(),

                Textarea::make('reason')
                    ->label('Alasan')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Tuliskan alasan pengajuan cuti/izin/sakit...'),

                Section::make('Persetujuan / Approval')
                    ->description('Diisi oleh Pengurus atau Super Administrator.')
                    ->visible(! $isSecurity)
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status Pengajuan')
                            ->options(LeaveRequest::getStatusLabels())
                            ->default(LeaveRequest::STATUS_PENDING)
                            ->required(),

                        Textarea::make('approval_notes')
                            ->label('Catatan Approval')
                            ->placeholder('Catatan persetujuan atau alasan penolakan...'),
                    ]),
            ]);
    }
}
