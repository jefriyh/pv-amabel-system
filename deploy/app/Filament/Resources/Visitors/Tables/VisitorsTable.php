<?php

namespace App\Filament\Resources\Visitors\Tables;

use App\Http\Controllers\Admin\MediaController;
use App\Models\Visitor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class VisitorsTable
{
    public static function configure(Table $table): Table
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser?->isSuperAdmin() ?? false;
        $canApprove = $isSuperAdmin || ($currentUser?->isPengurus() ?? false);

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('selfie_path')
                    ->label('Foto')
                    ->circular()
                    ->size(42)
                    ->state(fn ($record) => MediaController::urlFor($record, 'selfie_path'))
                    ->defaultImageUrl(null)
                    ->placeholder('-'),

                TextColumn::make('name')
                    ->label('Nama & Kunjungan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function (Visitor $record) {
                        $time = $record->created_at->translatedFormat('d M Y, H:i');
                        $host = $record->host_name ? " • Menemui: {$record->host_name}" : '';
                        $purpose = $record->purpose ? " | {$record->purpose}" : '';

                        return "{$time}{$host}{$purpose}";
                    })
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Visitor::getStatusLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        Visitor::STATUS_PENDING => 'warning',
                        Visitor::STATUS_APPROVED => 'success',
                        Visitor::STATUS_REJECTED => 'danger',
                        Visitor::STATUS_CHECKED_IN => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu Kedatangan')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('host_name')
                    ->label('Menemui')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('photos_purged_at')
                    ->label('Foto dihapus')
                    ->dateTime('d M Y')
                    ->placeholder('Masih tersimpan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Kunjungan')
                    ->options(Visitor::getStatusLabels()),

                Filter::make('periode')
                    ->schema([
                        DatePicker::make('dari')->label('Dari tanggal')->native(false),
                        DatePicker::make('sampai')->label('Sampai tanggal')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['sampai'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)))
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

                Filter::make('hari_ini')
                    ->label('Hanya hari ini')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today())),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Visitor $record): bool => $canApprove && $record->status === Visitor::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Persetujuan Tamu')
                    ->modalDescription(fn (Visitor $record) => "Setujui kunjungan {$record->name} ke {$record->host_name}?")
                    ->action(function (Visitor $record): void {
                        $record->update([
                            'status' => Visitor::STATUS_APPROVED,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Kunjungan tamu berhasil disetujui')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->visible(fn (Visitor $record): bool => $canApprove && $record->status === Visitor::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Kunjungan Tamu')
                    ->form([
                        Textarea::make('approval_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Contoh: Penghuni tidak ada di tempat / tidak menerima tamu.'),
                    ])
                    ->action(function (Visitor $record, array $data): void {
                        $record->update([
                            'status' => Visitor::STATUS_REJECTED,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'approval_notes' => $data['approval_notes'],
                        ]);

                        Notification::make()
                            ->title('Kunjungan tamu ditolak')
                            ->danger()
                            ->send();
                    }),

                Action::make('checkIn')
                    ->label('Tandai Masuk')
                    ->icon('heroicon-m-arrow-right-end-on-rectangle')
                    ->color('info')
                    ->visible(fn (Visitor $record): bool => $record->status === Visitor::STATUS_APPROVED)
                    ->action(function (Visitor $record): void {
                        $record->update(['status' => Visitor::STATUS_CHECKED_IN]);

                        Notification::make()
                            ->title('Tamu tercatat sudah masuk komplek')
                            ->info()
                            ->send();
                    }),

                ViewAction::make()->iconButton()->tooltip('Lihat Detail'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Ubah')
                    ->visible(fn (): bool => $isSuperAdmin),
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus')
                    ->visible(fn (): bool => $isSuperAdmin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => $isSuperAdmin),
                ]),
            ])
            ->emptyStateHeading('Belum ada tamu tercatat')
            ->emptyStateDescription('Entri akan muncul di sini begitu tamu mengisi form di gerbang.');
    }
}
