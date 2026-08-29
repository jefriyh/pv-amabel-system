<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-action-widget';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'user' => $user,
            'isSecurity' => $user?->isSecurity() ?? false,
            'isSuperAdmin' => $user?->isSuperAdmin() ?? false,
            'isPengurus' => $user?->isPengurus() ?? false,
        ];
    }
}
