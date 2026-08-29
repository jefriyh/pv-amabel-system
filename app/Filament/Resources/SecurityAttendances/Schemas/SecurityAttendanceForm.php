<?php

namespace App\Filament\Resources\SecurityAttendances\Schemas;

use App\Models\SecurityAttendance;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class SecurityAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $currentDay = $days[now()->dayOfWeek] ?? 'Hari Ini';

        // Aturan Pembulatan Jam Mulai Tugas:
        // >= 30 menit -> pembulatan ke atas (10:37 -> 11:00)
        // < 30 menit  -> pembulatan ke bawah (10:20 -> 10:00)
        $now = now();
        $roundedStart = ($now->minute >= 30)
            ? $now->copy()->addHour()->startOfHour()
            : $now->copy()->startOfHour();

        $defaultStartTime = $roundedStart->format('H:i');
        $defaultEndTime = $roundedStart->copy()->addHours(12)->format('H:i');

        return $schema
            ->components([
                Section::make('Log Presensi Kehadiran Security')
                    ->description('Waktu, hari, tanggal, dan lokasi dicatat otomatis oleh sistem.')
                    ->schema([
                        // Hidden values (tipe presensi & status kehadiran ditakeout dari UI)
                        Hidden::make('user_id')->default($user?->id),
                        Hidden::make('type')->default(SecurityAttendance::TYPE_MASUK),
                        Hidden::make('status')->default('hadir'),
                        Hidden::make('latitude'),
                        Hidden::make('longitude'),

                        // Baris Petugas: Security Saat Ini & Security Sebelumnya
                        Grid::make(2)
                            ->schema([
                                TextInput::make('current_user_name')
                                    ->label('Petugas Security Saat Ini')
                                    ->default($user?->name ?? 'Petugas Security')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('previous_security_id')
                                    ->label('Petugas Security Sebelumnya')
                                    ->placeholder('Pilih shift sebelumnya (opsional)')
                                    ->options(
                                        User::where('role', User::ROLE_SECURITY)
                                            ->when($user?->id, fn ($q) => $q->where('id', '!=', $user->id))
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable(),
                            ])
                            ->columnSpanFull(),

                        // Hari, Tanggal, dan Jam Presensi dicatat otomatis di latar belakang (disembunyikan dari UI)
                        Hidden::make('day_name')->default($currentDay),
                        Hidden::make('attendance_date')->default(now()->toDateString()),
                        Hidden::make('attendance_time')->default(now()->format('H:i:s')),

                        // Baris 2: Jam Mulai & Jam Selesai Tugas (Sejajar dan Sempurna)
                        Grid::make(2)
                            ->schema([
                                TimePicker::make('start_time')
                                    ->label('Jam Mulai Tugas')
                                    ->seconds(false)
                                    ->default($defaultStartTime)
                                    ->required()
                                    ->live()
                                    ->hint(fn () => now()->translatedFormat('d M Y'))
                                    ->hintColor('primary')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            try {
                                                $start = Carbon::parse($state);
                                                $set('end_time', $start->copy()->addHours(12)->format('H:i'));
                                            } catch (\Throwable $e) {
                                                // ignore
                                            }
                                        }
                                    }),

                                TimePicker::make('end_time')
                                    ->label('Jam Selesai Tugas')
                                    ->seconds(false)
                                    ->default($defaultEndTime)
                                    ->required()
                                    ->live()
                                    ->hint(function ($get) {
                                        $startStr = $get('start_time');
                                        $endStr = $get('end_time');
                                        if (! $startStr || ! $endStr) {
                                            return null;
                                        }
                                        try {
                                            $today = now();
                                            $start = Carbon::parse($startStr);
                                            $end = Carbon::parse($endStr);

                                            // Jika jam selesai lebih kecil / sama dengan jam mulai -> lewat tengah malam (Besok)
                                            if ($end->format('H:i') <= $start->format('H:i')) {
                                                $nextDay = $today->copy()->addDay();
                                                return 'Besok, ' . $nextDay->translatedFormat('d M Y');
                                            }

                                            return now()->translatedFormat('d M Y');
                                        } catch (\Throwable $e) {
                                            return null;
                                        }
                                    })
                                    ->hintColor(function ($get) {
                                        $startStr = $get('start_time');
                                        $endStr = $get('end_time');
                                        if ($startStr && $endStr) {
                                            try {
                                                return (Carbon::parse($endStr)->format('H:i') <= Carbon::parse($startStr)->format('H:i'))
                                                    ? 'warning'
                                                    : 'primary';
                                            } catch (\Throwable $e) {}
                                        }
                                        return 'info';
                                    }),
                            ])
                            ->columnSpanFull(),

                        // Informasi Total Durasi Jam Kerja (Live Reactive)
                        Placeholder::make('work_duration_info')
                            ->label('Total Durasi Jam Kerja')
                            ->content(function ($get) {
                                $startStr = $get('start_time');
                                $endStr = $get('end_time');
                                if (! $startStr || ! $endStr) {
                                    return '-';
                                }
                                try {
                                    $start = Carbon::parse($startStr);
                                    $end = Carbon::parse($endStr);
                                    $startMinutes = $start->hour * 60 + $start->minute;
                                    $endMinutes = $end->hour * 60 + $end->minute;

                                    if ($endMinutes <= $startMinutes) {
                                        $totalMinutes = (24 * 60 - $startMinutes) + $endMinutes;
                                        $isNight = true;
                                    } else {
                                        $totalMinutes = $endMinutes - $startMinutes;
                                        $isNight = false;
                                    }

                                    $hours = floor($totalMinutes / 60);
                                    $minutes = $totalMinutes % 60;
                                    $durText = $minutes > 0 ? "{$hours} Jam {$minutes} Menit" : "{$hours} Jam";
                                    $shiftBadge = $isNight ? '🌙 Shift Malam (+1 Hari)' : '☀️ Shift Hari Sama';

                                    return new HtmlString(
                                        "<div style='display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.85rem; border-radius: 0.5rem; background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; font-size: 0.825rem;'>
                                            <div style='display: flex; align-items: center; gap: 0.4rem;'>
                                                <span>⏱️</span>
                                                <strong>Total: {$durText}</strong>
                                            </div>
                                            <span style='font-size: 0.725rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 9999px; background-color: #dcfce7; color: #15803d;'>{$shiftBadge}</span>
                                        </div>"
                                    );
                                } catch (\Throwable $e) {
                                    return '-';
                                }
                            })
                            ->columnSpanFull(),

                        // Baris 3: Foto Selfie Wajah (Kamera Langsung WebRTC, Kamera HP Native & Galeri)
                        ViewField::make('selfie_path')
                            ->label('Foto Selfie Wajah (Kamera)')
                            ->view('filament.forms.components.camera-selfie-uploader')
                            ->required()
                            ->columnSpanFull(),

                        // Baris 4: Catatan Lokasi dengan Deteksi GPS Otomatis
                        ViewField::make('location_address')
                            ->label('Catatan Lokasi & Alamat Pos')
                            ->view('filament.forms.components.gps-location-detector')
                            ->default('Pos Gerbang Utama Villa Amabel')
                            ->columnSpanFull(),

                        // Baris 5: Catatan Tambahan (Opsional)
                        Textarea::make('notes')
                            ->label('Catatan Tambahan (Opsional)')
                            ->placeholder('Kondisi pos, serah terima HT/kunci, atau catatan tugas...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
