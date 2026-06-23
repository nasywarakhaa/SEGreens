<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.dashboard.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('admin.dashboard.subheading');
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
