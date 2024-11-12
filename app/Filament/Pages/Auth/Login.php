<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BasePage;

class Login extends BasePage
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'francos@francos.com',
            'password' => '01FrancosPizza',
            'remember' => true,
        ]);
    }
}
