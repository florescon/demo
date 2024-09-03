<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SizeProduct: string implements HasColor, HasIcon, HasLabel
{
    case Complete = 'complete';

    case Half = 'half';

    public function getLabel(): string
    {
        return match ($this) {
            self::Complete => 'Complete',
            self::Half => 'Half',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Complete => 'warning',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Complete => 'heroicon-m-x-circle',
            self::Half => 'heroicon-m-truck',
        };
    }
}
