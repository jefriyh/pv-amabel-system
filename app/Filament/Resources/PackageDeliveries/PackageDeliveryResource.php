<?php

namespace App\Filament\Resources\PackageDeliveries;

use App\Filament\Resources\PackageDeliveries\Pages\ListPackageDeliveries;
use App\Filament\Resources\PackageDeliveries\Pages\ViewPackageDelivery;
use App\Filament\Resources\PackageDeliveries\Schemas\PackageDeliveryInfolist;
use App\Filament\Resources\PackageDeliveries\Tables\PackageDeliveriesTable;
use App\Models\PackageDelivery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PackageDeliveryResource extends Resource
{
    protected static ?string $model = PackageDelivery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Paket';

    protected static ?string $modelLabel = 'paket';

    protected static ?string $pluralModelLabel = 'paket';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return PackageDeliveryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PackageDeliveriesTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) PackageDelivery::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Paket hari ini';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackageDeliveries::route('/'),
            'view' => ViewPackageDelivery::route('/{record}'),
        ];
    }
}
