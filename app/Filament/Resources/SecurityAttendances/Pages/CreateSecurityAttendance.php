<?php

namespace App\Filament\Resources\SecurityAttendances\Pages;

use App\Filament\Resources\SecurityAttendances\SecurityAttendanceResource;
use App\Http\Controllers\Admin\MediaController;
use App\Models\SecurityAttendance;
use App\Services\TelegramNotifier;
use App\Support\TelegramMessage;
use Filament\Notifications\Notification;
use Filament\Resources\Events\RecordCreated;
use Filament\Resources\Events\RecordSaved;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateSecurityAttendance extends CreateRecord
{
    protected static string $resource = SecurityAttendanceResource::class;

    protected static ?string $title = 'Presensi Kehadiran Security';

    protected static bool $canCreateAnother = false;

    protected string $view = 'filament.pages.create-security-attendance';

    public ?SecurityAttendance $createdAttendance = null;

    public bool $showSuccessModal = false;

    public ?string $whatsappUrl = null;

    public ?string $whatsappText = null;

    public ?string $selfieUrl = null;

    public function getMaxWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan Presensi');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user) {
            $data['user_id'] = $data['user_id'] ?? $user->id;
        }

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $now = now();
        $roundedStart = ($now->minute >= 30)
            ? $now->copy()->addHour()->startOfHour()
            : $now->copy()->startOfHour();

        $data['attendance_date'] = $now->toDateString();
        $data['day_name'] = $days[$now->dayOfWeek] ?? 'Hari Ini';
        $data['attendance_time'] = $now->format('H:i:s');
        $data['start_time'] = $data['start_time'] ?? $roundedStart->format('H:i');
        $data['end_time'] = $data['end_time'] ?? $roundedStart->copy()->addHours(12)->format('H:i');
        $data['type'] = $data['type'] ?? 'masuk';
        $data['status'] = $data['status'] ?? 'hadir';

        // Simpan file selfie jika berupa base64 data URI dari kamera langsung atau TemporaryUploadedFile
        if (isset($data['selfie_path']) && is_string($data['selfie_path']) && str_starts_with($data['selfie_path'], 'data:image')) {
            $imageParts = explode(';base64,', $data['selfie_path']);
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'jpg';
            if ($imageType === 'jpeg') {
                $imageType = 'jpg';
            }
            $imageBase64 = base64_decode($imageParts[1] ?? '');
            $filename = 'attendances/' . \Illuminate\Support\Str::random(40) . '.' . $imageType;
            \Illuminate\Support\Facades\Storage::disk('local')->put($filename, $imageBase64);
            $data['selfie_path'] = $filename;
        } elseif (isset($data['selfie_path']) && $data['selfie_path'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $data['selfie_path'] = $data['selfie_path']->store('attendances', 'local');
        }

        return $data;
    }

    public function create(bool $another = false): void
    {
        if ($this->isCreating) {
            return;
        }

        $this->isCreating = true;
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');
            $data = $this->form->getState();
            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);
            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);
            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');
            Event::dispatch(RecordCreated::class, ['record' => $this->record, 'data' => $data, 'page' => $this]);
            Event::dispatch(RecordSaved::class, ['record' => $this->record, 'data' => $data, 'page' => $this]);
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();

            $this->isCreating = false;

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();
            $this->isCreating = false;

            throw $exception;
        }

        $this->commitDatabaseTransaction();
        $this->rememberData();

        Notification::make()
            ->title('Presensi Berhasil Dicatat')
            ->success()
            ->send();

        // Tampilkan Popup Sukses dengan Ringkasan & Tombol Kirim WhatsApp Screenshot
        $this->record->loadMissing(['user', 'previousSecurity']);
        $this->createdAttendance = $this->record;
        $this->selfieUrl = MediaController::urlFor($this->record, 'selfie_path');
        $this->whatsappText = $this->generateWhatsAppText($this->record);
        $this->whatsappUrl = 'https://api.whatsapp.com/send?text=' . urlencode($this->whatsappText);
        $this->showSuccessModal = true;
        $this->isCreating = false;
    }

    public function closeModalAndRedirect(): void
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }

    public function generateWhatsAppText(SecurityAttendance $attendance): string
    {
        $complexName = config('guestbook.complex_name', 'Villa Amabel');
        $securityName = $attendance->user?->name ?? 'Petugas Security';
        $timeStr = Carbon::parse($attendance->attendance_time)->format('H:i') . ' WIB';
        $dateStr = Carbon::parse($attendance->attendance_date)->translatedFormat('l, d F Y');

        $shiftInfo = '-';
        if ($attendance->start_time && $attendance->end_time) {
            $start = Carbon::parse($attendance->start_time)->format('H:i');
            $end = Carbon::parse($attendance->end_time)->format('H:i');
            $startDate = $attendance->attendance_date ? Carbon::parse($attendance->attendance_date) : now();

            if ($end <= $start) {
                $endDate = $startDate->copy()->addDay();
                $shiftInfo = "{$start} ({$startDate->translatedFormat('d M')}) - {$end} ({$endDate->translatedFormat('d M')}) [Shift Malam]";
            } else {
                $shiftInfo = "{$start} - {$end} ({$startDate->translatedFormat('d M')})";
            }
        }

        $detailUrl = url('/internal/security-attendances/' . $attendance->id);
        $prevSecurityName = $attendance->previousSecurity?->name ?? '-';

        $lines = [
            "🛡️ *LOG PRESENSI KEHADIRAN SECURITY*",
            "_{$complexName}_",
            "",
            "👤 *Petugas Bertugas:* {$securityName}",
            "🤝 *Petugas Sebelumnya:* {$prevSecurityName}",
            "📅 *Hari, Tanggal:* {$dateStr}",
            "⏰ *Jam Presensi:* {$timeStr}",
            "🕒 *Jam Tugas Shift:* {$shiftInfo}",
            "⏱️ *Total Durasi Kerja:* {$attendance->work_duration}",
        ];

        if (filled($attendance->notes)) {
            $lines[] = "📝 *Catatan:* {$attendance->notes}";
        }

        return implode("\n", $lines);
    }

    protected function afterCreate(): void
    {
        /** @var SecurityAttendance $attendance */
        $attendance = $this->getRecord();
        $attendance->loadMissing(['user', 'previousSecurity']);

        try {
            $telegram = app(TelegramNotifier::class);
            if ($telegram->isConfigured()) {
                $detailUrl = url('/internal/security-attendances/' . $attendance->id);
                $html = TelegramMessage::forAttendance($attendance, $detailUrl);
                $telegram->sendQuietly($html);
            }
        } catch (Throwable $e) {
            Log::error('Gagal mengirim notifikasi presensi ke Telegram: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
