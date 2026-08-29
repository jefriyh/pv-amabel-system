<?php

namespace App\Filament\Widgets;

use App\Models\PackageDelivery;
use App\Models\Visitor;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WeeklyActivityChart extends ChartWidget
{
    protected ?string $heading = 'Aktivitas Tamu & Paket (14 Hari)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $ago) => today()->subDays($ago));

        return [
            'datasets' => [
                [
                    'label' => 'Tamu',
                    'data' => $days->map(fn (Carbon $day) => Visitor::whereDate('created_at', $day)->count())->all(),
                    'borderColor' => '#5F8575',
                    'backgroundColor' => 'rgba(95, 133, 117, 0.12)',
                    'fill' => true,
                ],
                [
                    'label' => 'Paket',
                    'data' => $days->map(fn (Carbon $day) => PackageDelivery::whereDate('created_at', $day)->count())->all(),
                    'borderColor' => '#93B5A6',
                    'backgroundColor' => 'rgba(147, 181, 166, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn (Carbon $day) => $day->format('d/m'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
