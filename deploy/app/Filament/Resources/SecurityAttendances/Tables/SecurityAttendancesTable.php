<?php

namespace App\Filament\Resources\SecurityAttendances\Tables;

use App\Http\Controllers\Admin\MediaController;
use App\Models\SecurityAttendance;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SecurityAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('selfie_path')
                    ->label('Foto')
                    ->circular()
                    ->size(38)
                    ->state(fn ($record) => MediaController::urlFor($record, 'selfie_path'))
                    ->placeholder('-'),

                TextColumn::make('user.name')
                    ->label('Petugas & Presensi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function ($record) {
                        $dateStr = $record->attendance_date ? Carbon::parse($record->attendance_date)->translatedFormat('d M Y') : $record->created_at->translatedFormat('d M Y');
                        $timeStr = $record->attendance_time ? Carbon::parse($record->attendance_time)->format('H:i') . ' WIB' : $record->created_at->format('H:i') . ' WIB';
                        
                        $shiftStr = '';
                        if ($record->start_time && $record->end_time) {
                            $startH = Carbon::parse($record->start_time)->format('H:i');
                            $endH = Carbon::parse($record->end_time)->format('H:i');
                            $shiftStr = " • Shift: {$startH} - {$endH}";
                        }

                        $handover = $record->previousSecurity ? " | Dari: {$record->previousSecurity->name}" : '';

                        return "{$record->day_name}, {$dateStr} {$timeStr}{$shiftStr}{$handover}";
                    })
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('location_address')
                    ->label('Lokasi Pos')
                    ->placeholder('Pos Gerbang')
                    ->description(fn ($record) => ($record->latitude && $record->longitude) ? "GPS: " . number_format($record->latitude, 4) . ", " . number_format($record->longitude, 4) : null)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Waktu Sistem')
                    ->dateTime('d M Y H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->placeholder('-')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Presensi')
                    ->options(SecurityAttendance::getTypeLabels()),

                SelectFilter::make('user_id')
                    ->label('Petugas Security')
                    ->options(User::where('role', User::ROLE_SECURITY)->pluck('name', 'id'))
                    ->visible(fn (): bool => ! (auth()->user()?->isSecurity() ?? false)),

                Filter::make('periode')
                    ->schema([
                        DatePicker::make('dari')->label('Dari tanggal')->native(false),
                        DatePicker::make('sampai')->label('Sampai tanggal')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $date) => $q->whereDate('attendance_date', '>=', $date))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $date) => $q->whereDate('attendance_date', '<=', $date)))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'] ?? null) {
                            $indicators[] = 'Dari ' . Carbon::parse($data['dari'])->format('d M Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators[] = 'Sampai ' . Carbon::parse($data['sampai'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('share_whatsapp')
                    ->iconButton()
                    ->tooltip('Bagikan Screenshot ke WhatsApp')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->modalHeading('Bagikan Ringkasan Presensi ke WhatsApp')
                    ->modalWidth('lg')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (SecurityAttendance $record) => view('filament.components.attendance-share-modal', [
                        'record' => $record->loadMissing(['user', 'previousSecurity']),
                    ])),
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Lihat Detail'),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                ]),
            ])
            ->emptyStateHeading('Belum ada log kehadiran')
            ->emptyStateDescription('Petugas security dapat mencatat presensi masuk/keluar/patroli dengan tombol di atas.');
    }
}
