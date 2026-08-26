<?php

namespace App\Filament\Resources\Visitors;

use App\Filament\Resources\Visitors\Pages\ListVisitors;
use App\Filament\Resources\Visitors\Pages\ViewVisitor;
use App\Filament\Resources\Visitors\Schemas\VisitorInfolist;
use App\Filament\Resources\Visitors\Tables\VisitorsTable;
use App\Models\Visitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Tamu';

    protected static ?string $modelLabel = 'tamu';

    protected static ?string $pluralModelLabel = 'tamu';

    protected static ?int $navigationSort = 1;

    /**
     * Data tamu hanya boleh lahir dari form publik di gerbang. Kalau admin bisa
     * mengarang atau menyunting entri, catatan ini kehilangan nilainya sebagai bukti.
     */
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
        return VisitorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitorsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Visitor::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tamu hari ini';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitors::route('/'),
            'view' => ViewVisitor::route('/{record}'),
        ];
    }
}
