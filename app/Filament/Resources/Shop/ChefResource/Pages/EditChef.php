<?php

namespace App\Filament\Resources\Shop\ChefResource\Pages;

use App\Filament\Resources\Shop\ChefResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChef extends EditRecord
{
    protected static string $resource = ChefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
