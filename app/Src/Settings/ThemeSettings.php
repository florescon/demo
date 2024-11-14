<?php

namespace App\Src\Settings;

use Spatie\LaravelSettings\Settings;

class ThemeSettings extends Settings
{
    public ?string $app_banner;

    public ?string $primary;

    public ?string $secondary;

    public static function group(): string
    {
        return 'theme';
    }
}
