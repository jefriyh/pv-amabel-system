<?php

namespace App\Filament\Resources\Visitors\Tables;

use App\Http\Controllers\Admin\MediaController;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class VisitorsTable
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

                ImageColumn::make('selfie_path')
                    ->label('Selfie')
                    ->circular()
                    // Foto disimpan di disk privat, jadi state-nya diisi URL route
                    // ber-auth kita sendiri, bukan URL disk.
                    ->state(fn ($record) => MediaController::urlFor($record, 'selfie_path'))
                    ->defaultImageUrl(null)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('host_name')
                    ->label('Menemui')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('photos_purged_at')
                    ->label('Foto dihapus')
                    ->dateTime('d M Y')
                    ->placeholder('Masih tersimpan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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

                Filter::make('hari_ini')
                    ->label('Hanya hari ini')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today())),
            ])
            ->recordActions([
                ViewAction::make()->label('Detail'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada tamu tercatat')
            ->emptyStateDescription('Entri akan muncul di sini begitu tamu mengisi form di gerbang.');
    }
}
