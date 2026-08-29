<?php

namespace App\Filament\Resources\Visitors;

use App\Filament\Resources\Visitors\Pages\EditVisitor;
use App\Filament\Resources\Visitors\Pages\ListVisitors;
use App\Filament\Resources\Visitors\Pages\ViewVisitor;
use App\Filament\Resources\Visitors\Schemas\VisitorForm;
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

    protected static ?int $navigationSort = 3;

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
        return VisitorForm::configure($schema);
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
        $pending = Visitor::where('status', Visitor::STATUS_PENDING)->count();
        if ($pending > 0) {
            return (string) $pending;
        }

        return (string) Visitor::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return Visitor::where('status', Visitor::STATUS_PENDING)->exists() ? 'warning' : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $pending = Visitor::where('status', Visitor::STATUS_PENDING)->count();
        if ($pending > 0) {
            return "{$pending} tamu menunggu approval";
        }

        return 'Tamu hari ini';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitors::route('/'),
            'view' => ViewVisitor::route('/{record}'),
            'edit' => EditVisitor::route('/{record}/edit'),
        ];
    }
}
