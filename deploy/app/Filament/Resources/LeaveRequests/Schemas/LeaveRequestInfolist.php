<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use App\Http\Controllers\Admin\MediaController;
use App\Models\LeaveRequest;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pengajuan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('Nama Security')->weight('bold'),
                        TextEntry::make('created_at')->label('Waktu Pengajuan')->dateTime('l, d F Y H:i'),
                        TextEntry::make('type_label')->label('Jenis Pengajuan')->badge(),
                        TextEntry::make('total_days')->label('Total Durasi')->suffix(' Hari'),
                        TextEntry::make('formatted_selected_dates')->label('Tanggal yang Dipilih')->columnSpanFull(),
                        TextEntry::make('reason')->label('Alasan')->columnSpanFull(),
                    ]),

                Section::make('Lampiran / Dokumen')
                    ->columns(1)
                    ->schema([
                        ImageEntry::make('attachment_path')
                            ->label('Bukti Dokumen / Surat Dokter')
                            ->state(fn (LeaveRequest $record) => MediaController::urlFor($record, 'attachment_path'))
                            ->placeholder('Tidak ada lampiran')
                            ->extraImgAttributes([
                                'class' => 'rounded-xl object-contain shadow-sm max-w-full max-h-[380px] w-auto h-auto mx-auto border border-slate-200 dark:border-slate-700 block',
                                'style' => 'max-width: 100% !important; height: auto !important; max-height: 380px !important; object-fit: contain !important; margin: 0 auto !important;',
                            ])
                            ->extraAttributes(['class' => 'overflow-hidden flex justify-center items-center w-full']),
                    ]),

                Section::make('Status Persetujuan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status_label')
                            ->label('Status')
                            ->badge()
                            ->color(fn (LeaveRequest $record): string => match ($record->status) {
                                LeaveRequest::STATUS_APPROVED => 'success',
                                LeaveRequest::STATUS_REJECTED => 'danger',
                                default => 'warning',
                            }),
                        TextEntry::make('approver.name')->label('Penyetuju')->placeholder('-'),
                        TextEntry::make('approved_at')->label('Waktu Diputuskan')->dateTime('d F Y H:i')->placeholder('-'),
                        TextEntry::make('approval_notes')->label('Catatan Approval')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }
}
