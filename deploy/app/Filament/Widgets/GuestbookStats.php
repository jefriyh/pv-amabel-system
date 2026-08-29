<?php

namespace App\Filament\Widgets;

use App\Models\LeaveRequest;
use App\Models\PackageDelivery;
use App\Models\SecurityAttendance;
use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuestbookStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $isSecurity = $user?->isSecurity();

        if ($isSecurity) {
            $hasCheckedIn = SecurityAttendance::where('user_id', $user->id)
                ->whereDate('attendance_date', today())
                ->where('type', SecurityAttendance::TYPE_MASUK)
                ->exists();

            $pendingLeaves = LeaveRequest::where('user_id', $user->id)
                ->where('status', LeaveRequest::STATUS_PENDING)
                ->count();

            $packagesToday = PackageDelivery::whereDate('created_at', today())->count();
            $totalPackages = PackageDelivery::count();
            $visitorsToday = Visitor::whereDate('created_at', today())->count();

            return [
                Stat::make('Presensi Hari Ini', $hasCheckedIn ? 'Sudah Presensi Masuk' : 'Belum Presensi')
                    ->description($hasCheckedIn ? 'Tercatat di pos gerbang' : 'Silakan klik Presensi Sekarang')
                    ->descriptionIcon($hasCheckedIn ? 'heroicon-m-check-badge' : 'heroicon-m-exclamation-triangle')
                    ->color($hasCheckedIn ? 'success' : 'danger'),

                Stat::make('Sisa Cuti Tahunan', $user->remaining_leave_quota . ' Hari')
                    ->description($pendingLeaves > 0 ? "{$pendingLeaves} pengajuan pending" : 'Jatah kuota ' . $user->annual_leave_quota . ' hari')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('info'),

                Stat::make('Tamu Hari Ini', $visitorsToday)
                    ->description('Total pengunjung yang mengisi buku tamu')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary'),

                Stat::make('Jumlah Paket yang Didrop', $packagesToday)
                    ->description($totalPackages > 0 ? "Hari ini: {$packagesToday} • Total: {$totalPackages} paket" : 'Tercatat di kotak paket gerbang')
                    ->descriptionIcon('heroicon-m-archive-box')
                    ->color('primary'),
            ];
        }

        // Untuk Super Admin & Pengurus
        $visitorsToday = Visitor::whereDate('created_at', today())->count();
        $visitorsPending = Visitor::where('status', Visitor::STATUS_PENDING)->count();

        $totalPackages = PackageDelivery::count();
        $packagesToday = PackageDelivery::whereDate('created_at', today())->count();

        $securityPresentToday = SecurityAttendance::whereDate('attendance_date', today())
            ->where('type', SecurityAttendance::TYPE_MASUK)
            ->distinct('user_id')
            ->count('user_id');

        $leavesPending = LeaveRequest::where('status', LeaveRequest::STATUS_PENDING)->count();

        return [
            Stat::make('Tamu Hari Ini', $visitorsToday)
                ->description($visitorsPending > 0 ? "{$visitorsPending} tamu menunggu approval" : 'Semua telah ditinjau')
                ->descriptionIcon($visitorsPending > 0 ? 'heroicon-m-clock' : 'heroicon-m-user-group')
                ->color($visitorsPending > 0 ? 'warning' : 'success'),

            Stat::make('Jumlah Paket yang Didrop', $packagesToday)
                ->description($totalPackages > 0 ? "Hari ini: {$packagesToday} • Total: {$totalPackages} paket" : 'Tercatat di kotak paket gerbang')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Security Hadir Hari Ini', $securityPresentToday . ' Petugas')
                ->description('Tercatat presensi masuk di sistem')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color($securityPresentToday > 0 ? 'success' : 'gray'),

            Stat::make('Pengajuan Cuti / Izin', $leavesPending . ' Pending')
                ->description($leavesPending > 0 ? 'Memerlukan persetujuan pengurus' : 'Tidak ada pengajuan pending')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($leavesPending > 0 ? 'danger' : 'gray'),
        ];
    }
}
