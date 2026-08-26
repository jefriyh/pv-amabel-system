<?php

namespace App\Filament\Widgets;

use App\Models\PackageDelivery;
use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuestbookStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $visitorsToday = Visitor::whereDate('created_at', today())->count();
        $visitorsYesterday = Visitor::whereDate('created_at', today()->subDay())->count();
        $packagesToday = PackageDelivery::whereDate('created_at', today())->count();

        return [
            Stat::make('Tamu hari ini', $visitorsToday)
                ->description($this->compare($visitorsToday, $visitorsYesterday))
                ->descriptionIcon($visitorsToday >= $visitorsYesterday ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($visitorsToday >= $visitorsYesterday ? 'success' : 'gray'),

            Stat::make('Paket hari ini', $packagesToday)
                ->description('Dititipkan di kotak paket')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Tamu 7 hari terakhir', Visitor::where('created_at', '>=', now()->subDays(7))->count())
                ->description('Termasuk hari ini')
                ->descriptionIcon('heroicon-m-calendar-days'),
        ];
    }

    private function compare(int $today, int $yesterday): string
    {
        $diff = $today - $yesterday;

        return match (true) {
            $diff > 0 => "{$diff} lebih banyak dari kemarin",
            $diff < 0 => abs($diff).' lebih sedikit dari kemarin',
            default => 'Sama dengan kemarin',
        };
    }
}
