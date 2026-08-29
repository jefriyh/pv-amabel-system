<?php

namespace App\Filament\Resources\SecurityAttendances;

use App\Filament\Resources\SecurityAttendances\Pages\CreateSecurityAttendance;
use App\Filament\Resources\SecurityAttendances\Pages\ListSecurityAttendances;
use App\Filament\Resources\SecurityAttendances\Pages\ViewSecurityAttendance;
use App\Filament\Resources\SecurityAttendances\Schemas\SecurityAttendanceForm;
use App\Filament\Resources\SecurityAttendances\Schemas\SecurityAttendanceInfolist;
use App\Filament\Resources\SecurityAttendances\Tables\SecurityAttendancesTable;
use App\Models\SecurityAttendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SecurityAttendanceResource extends Resource
{
    protected static ?string $model = SecurityAttendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Log Kehadiran Security';

    protected static ?string $modelLabel = 'kehadiran security';

    protected static ?string $pluralModelLabel = 'log kehadiran security';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SecurityAttendanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SecurityAttendanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecurityAttendancesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurityAttendances::route('/'),
            'create' => CreateSecurityAttendance::route('/create'),
            'view' => ViewSecurityAttendance::route('/{record}'),
        ];
    }
}
