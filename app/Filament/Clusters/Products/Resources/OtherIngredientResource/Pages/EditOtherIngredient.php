<?php

namespace App\Filament\Clusters\Products\Resources\OtherIngredientResource\Pages;

use App\Filament\Clusters\Products\Resources\OtherIngredientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOtherIngredient extends EditRecord
{
    protected static string $resource = OtherIngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
