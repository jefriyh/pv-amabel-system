<?php

namespace App\Filament\Resources\PackageDeliveries\Tables;

use App\Http\Controllers\Admin\MediaController;
use Filament\Actions\BulkActionGroup;
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

class PackageDeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->description(fn ($record) => $record->created_at->diffForHumans())
                    ->sortable(),

                ImageColumn::make('photo_path')
                    ->label('Paket')
                    ->square()
                    ->state(fn ($record) => MediaController::urlFor($record, 'photo_path'))
                    ->toggleable(),

                TextColumn::make('courier_name')
                    ->label('Kurir')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('courier_company')
                    ->label('Ekspedisi')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('recipient_note')
                    ->label('Untuk')
                    ->searchable()
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('tracking_number')
                    ->label('Resi')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('photos_purged_at')
                    ->label('Foto dihapus')
                    ->dateTime('d M Y')
                    ->placeholder('Masih tersimpan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                            $indicators[] = 'Dari '.Carbon::parse($data['dari'])->format('d M Y');
                        }

                        if ($data['sampai'] ?? null) {
                            $indicators[] = 'Sampai '.Carbon::parse($data['sampai'])->format('d M Y');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make()->label('Detail'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada paket tercatat')
            ->emptyStateDescription('Entri akan muncul di sini begitu kurir mengisi form drop-off.');
    }
}
