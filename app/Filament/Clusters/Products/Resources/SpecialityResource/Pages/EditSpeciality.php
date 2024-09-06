<?php

namespace App\Filament\Clusters\Products\Resources\SpecialityResource\Pages;

use App\Filament\Clusters\Products\Resources\SpecialityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpeciality extends EditRecord
{
    protected static string $resource = SpecialityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
