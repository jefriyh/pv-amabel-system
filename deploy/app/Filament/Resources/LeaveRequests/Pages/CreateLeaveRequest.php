<?php

namespace App\Filament\Resources\LeaveRequests\Pages;

use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\TelegramNotifier;
use App\Support\TelegramMessage;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected static bool $canCreateAnother = false;

    public function getMaxWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan Pengajuan');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pengajuan cuti baru';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $user = auth()->user();
        if ($user) {
            $year = now()->year;
            return "Sisa {$user->remaining_leave_quota} dari total {$user->annual_leave_quota} hari untuk tahun {$year}.";
        }

        return null;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user && $user->isSecurity()) {
            $data['user_id'] = $user->id;
            $data['status'] = 'pending';
        }

        $type = $data['type'] ?? LeaveRequest::TYPE_CUTI;
        $minDate = ($type === LeaveRequest::TYPE_IZIN_DARURAT || $type === LeaveRequest::TYPE_SAKIT)
            ? now()->subDays(14)->toDateString()
            : now()->toDateString();

        if (! empty($data['selected_dates']) && is_array($data['selected_dates'])) {
            $dates = collect($data['selected_dates'])
                ->filter(fn ($d) => ! empty($d) && $d >= $minDate)
                ->sort()
                ->values();

            if ($dates->isEmpty()) {
                $dates = collect([now()->toDateString()]);
            }

            $data['selected_dates'] = $dates->all();
            $data['total_days'] = max(1, $dates->count());
            $data['start_date'] = $dates->first();
            $data['end_date'] = $dates->last();
        } elseif (! empty($data['start_date']) && ! empty($data['end_date'])) {
            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);
            $data['total_days'] = max(1, $start->diffInDays($end) + 1);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var LeaveRequest $leave */
        $leave = $this->getRecord();
        $leave->loadMissing('user');

        try {
            $telegram = app(TelegramNotifier::class);
            if ($telegram->isConfigured()) {
                $detailUrl = url('/internal/leave-requests/' . $leave->id);
                $html = TelegramMessage::forLeaveRequest($leave, $detailUrl);
                $telegram->sendQuietly($html);
            }
        } catch (Throwable $e) {
            Log::error('Gagal mengirim notifikasi pengajuan cuti/izin ke Telegram: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
