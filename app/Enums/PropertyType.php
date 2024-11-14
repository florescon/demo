<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PropertyType: string implements HasLabel
{
    case SALE = 'sale';
    case RENT = 'rent';

    public function getLabel(): string
    {
        return match ($this) {
            self::SALE => 'Sale',
            self::RENT => 'Rent',
        };
    }
}
