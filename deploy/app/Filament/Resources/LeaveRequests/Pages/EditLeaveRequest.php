<?php

namespace App\Filament\Resources\LeaveRequests\Pages;

use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Models\LeaveRequest;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
