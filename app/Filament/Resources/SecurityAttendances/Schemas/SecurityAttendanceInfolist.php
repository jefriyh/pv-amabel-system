<?php

namespace App\Filament\Resources\SecurityAttendances\Schemas;

use App\Http\Controllers\Admin\MediaController;
use App\Models\SecurityAttendance;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class SecurityAttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Kehadiran')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('Petugas Security Saat Ini')->weight('bold'),
                        TextEntry::make('previousSecurity.name')->label('Petugas Security Sebelumnya (Serah Terima)')->placeholder('-'),
                        TextEntry::make('shift_hours')->label('Jam Tugas & Jadwal Shift')
                            ->html()
                            ->state(function ($record) {
                                if (! $record->start_time || ! $record->end_time) {
                                    return '<span class="text-slate-500">-</span>';
                                }
                                $start = Carbon::parse($record->start_time)->format('H:i');
                                $end = Carbon::parse($record->end_time)->format('H:i');
                                $startDate = $record->attendance_date ? Carbon::parse($record->attendance_date) : now();

                                if ($end <= $start) {
                                    $endDate = $startDate->copy()->addDay();
                                    $text = "{$startDate->translatedFormat('d M Y')}, {$start} WIB - {$endDate->translatedFormat('d M Y')}, {$end} WIB";
                                    $bg = '#fffbeb';
                                    $border = '#fde68a';
                                    $color = '#92400e';
                                } else {
                                    $text = "{$startDate->translatedFormat('d M Y')}, {$start} - {$end} WIB";
                                    $bg = '#f0fdf4';
                                    $border = '#bbf7d0';
                                    $color = '#166534';
                                }

                                return '<span style="display: inline-block; white-space: normal; word-break: break-word; line-height: 1.45; padding: 0.35rem 0.75rem; border-radius: 9999px; background-color: ' . $bg . '; border: 1px solid ' . $border . '; color: ' . $color . '; font-size: 0.8125rem; font-weight: 600;">' . $text . '</span>';
                            })
                            ->columnSpanFull(),
                        TextEntry::make('attendance_date')->label('Tanggal')->date('d F Y'),
                        TextEntry::make('day_name')->label('Hari'),
                        TextEntry::make('attendance_time')->label('Jam Presensi')->time('H:i'),
                        TextEntry::make('work_duration')->label('Total Durasi Kerja')->badge()->color('info')->icon('heroicon-m-clock'),
                        TextEntry::make('status')->label('Status')->badge()->color('success'),
                        TextEntry::make('location_address')->label('Catatan Pos / Lokasi')->columnSpanFull(),
                        TextEntry::make('latitude')->label('Latitude GPS')->placeholder('-'),
                        TextEntry::make('longitude')->label('Longitude GPS')->placeholder('-'),
                        TextEntry::make('notes')->label('Catatan Tambahan')->placeholder('-')->columnSpanFull(),
                    ]),

                Section::make('Foto Selfie Wajah')
                    ->columns(1)
                    ->schema([
                        ImageEntry::make('selfie_path')
                            ->label('Foto Bukti Presensi')
                            ->state(fn (SecurityAttendance $record) => MediaController::urlFor($record, 'selfie_path'))
                            ->extraImgAttributes([
                                'class' => 'rounded-xl object-contain shadow-sm max-w-full max-h-[380px] w-auto h-auto mx-auto border border-slate-200 dark:border-slate-700 block',
                                'style' => 'max-width: 100% !important; height: auto !important; max-height: 380px !important; object-fit: contain !important; margin: 0 auto !important;',
                            ])
                            ->extraAttributes(['class' => 'overflow-hidden flex justify-center items-center w-full']),
                    ]),
            ]);
    }
}
