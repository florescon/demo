<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderPriority: string implements HasColor, HasIcon, HasLabel
{
    case Low = 'low';

    case Medium = 'medium';

    case High = 'high';

    public function getLabel(): string
    {
        return match ($this) {
            self::Low => __('Low'),
            self::Medium => __('Average'),
            self::High => __('High'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Low => 'warning',
            self::Medium => 'info',
            self::High => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Low => 'heroicon-m-arrow-path',
            self::Medium => 'heroicon-m-sparkles',
            self::High => 'heroicon-m-x-circle',
        };
    }
}
