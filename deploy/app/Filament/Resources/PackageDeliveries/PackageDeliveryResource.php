<?php

namespace App\Filament\Resources\PackageDeliveries;

use App\Filament\Resources\PackageDeliveries\Pages\EditPackageDelivery;
use App\Filament\Resources\PackageDeliveries\Pages\ListPackageDeliveries;
use App\Filament\Resources\PackageDeliveries\Pages\ViewPackageDelivery;
use App\Filament\Resources\PackageDeliveries\Schemas\PackageDeliveryForm;
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

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canDelete(mixed $record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return PackageDeliveryForm::configure($schema);
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
        $dititipkan = PackageDelivery::where('status', PackageDelivery::STATUS_DITITIPKAN)->count();
        if ($dititipkan > 0) {
            return (string) $dititipkan;
        }

        return (string) PackageDelivery::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $dititipkan = PackageDelivery::where('status', PackageDelivery::STATUS_DITITIPKAN)->count();
        if ($dititipkan > 0) {
            return "{$dititipkan} paket belum diambil";
        }

        return 'Paket hari ini';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackageDeliveries::route('/'),
            'view' => ViewPackageDelivery::route('/{record}'),
            'edit' => EditPackageDelivery::route('/{record}/edit'),
        ];
    }
}
