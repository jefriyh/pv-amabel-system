<?php

namespace App\Filament\Resources\PackageDeliveries\Tables;

use App\Http\Controllers\Admin\MediaController;
use App\Models\PackageDelivery;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PackageDeliveriesTable
{
    public static function configure(Table $table): Table
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser?->isSuperAdmin() ?? false;

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular()
                    ->size(42)
                    ->state(fn ($record) => MediaController::urlFor($record, 'photo_path'))
                    ->placeholder('-'),

                TextColumn::make('courier_name')
                    ->label('Kurir & Paket')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function (PackageDelivery $record) {
                        $time = $record->created_at->translatedFormat('d M Y, H:i');
                        $company = $record->courier_company ? " ({$record->courier_company})" : '';
                        $recipient = $record->recipient_note ? " • Untuk: {$record->recipient_note}" : '';
                        $tracking = $record->tracking_number ? " | Resi: {$record->tracking_number}" : '';

                        return "{$time}{$company}{$recipient}{$tracking}";
                    })
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PackageDelivery::getStatusLabels()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        PackageDelivery::STATUS_DITITIPKAN => 'info',
                        PackageDelivery::STATUS_DITERIMA => 'success',
                        PackageDelivery::STATUS_DIKEMBALIKAN => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu Terima')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('recipient_note')
                    ->label('Penerima')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tracking_number')
                    ->label('No. Resi')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('received_by')
                    ->label('Penerima')
                    ->placeholder('Belum diambil')
                    ->toggleable(),

                TextColumn::make('photos_purged_at')
                    ->label('Foto dihapus')
                    ->dateTime('d M Y')
                    ->placeholder('Masih tersimpan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Paket')
                    ->options(PackageDelivery::getStatusLabels()),

                SelectFilter::make('courier_company')
                    ->label('Ekspedisi')
                    ->options(fn () => array_combine(config('guestbook.couriers'), config('guestbook.couriers'))),

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
            ])
            ->recordActions([
                Action::make('handover')
                    ->label('Serah Terima')
                    ->icon('heroicon-m-hand-raised')
                    ->color('success')
                    ->visible(fn (PackageDelivery $record): bool => $record->status === PackageDelivery::STATUS_DITITIPKAN)
                    ->modalHeading('Serah Terima Paket ke Penghuni')
                    ->modalDescription(fn (PackageDelivery $record) => "Paket dari {$record->courier_company} ({$record->recipient_note}) diambil oleh:")
                    ->form([
                        TextInput::make('received_by')
                            ->label('Nama Penerima / Penghuni')
                            ->required()
                            ->placeholder('Contoh: Ibu Linda / Asisten Rumah Tangga'),
                    ])
                    ->action(function (PackageDelivery $record, array $data): void {
                        $record->update([
                            'status' => PackageDelivery::STATUS_DITERIMA,
                            'received_by' => $data['received_by'],
                            'received_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Paket berhasil diserahkan ke penghuni')
                            ->success()
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
            ->emptyStateHeading('Belum ada paket tercatat')
            ->emptyStateDescription('Entri akan muncul di sini begitu kurir mengisi form drop-off.');
    }
}
