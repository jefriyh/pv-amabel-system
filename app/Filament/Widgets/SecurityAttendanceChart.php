<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use App\Models\SecurityAttendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SecurityAttendanceChart extends ChartWidget
{
    protected ?string $heading = 'Aktivitas Presensi Security (14 Hari)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $user = auth()->user();
        $isSecurity = $user?->isSecurity();

        $days = collect(range(13, 0))->map(fn (int $ago) => today()->subDays($ago));

        if ($isSecurity) {
            $attendanceData = $days->map(function (Carbon $day) use ($user) {
                return SecurityAttendance::where('user_id', $user->id)
                    ->whereDate('attendance_date', $day)
                    ->where('type', SecurityAttendance::TYPE_MASUK)
                    ->count();
            })->all();

            return [
                'datasets' => [
                    [
                        'label' => 'Presensi Masuk Saya',
                        'data' => $attendanceData,
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $days->map(fn (Carbon $day) => $day->format('d/m'))->all(),
            ];
        }

        // Untuk Super Admin & Pengurus: Total Security Hadir & Cuti/Izin per Hari
        $presentData = $days->map(function (Carbon $day) {
            return SecurityAttendance::whereDate('attendance_date', $day)
                ->where('type', SecurityAttendance::TYPE_MASUK)
                ->distinct('user_id')
                ->count('user_id');
        })->all();

        $leaveData = $days->map(function (Carbon $day) {
            $dateStr = $day->toDateString();

            return LeaveRequest::where('status', LeaveRequest::STATUS_APPROVED)
                ->where(function ($q) use ($dateStr) {
                    $q->whereJsonContains('selected_dates', $dateStr)
                        ->orWhere(function ($q2) use ($dateStr) {
                            $q2->whereDate('start_date', '<=', $dateStr)
                                ->whereDate('end_date', '>=', $dateStr);
                        });
                })
                ->count();
        })->all();

        return [
            'datasets' => [
                [
                    'label' => 'Security Hadir (Bertugas)',
                    'data' => $presentData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.25)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Security Cuti / Izin',
                    'data' => $leaveData,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                    'tension' => 0.3,
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
