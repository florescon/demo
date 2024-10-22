<?php

namespace App\Filament\Clusters\Products\Resources\OtherIngredientResource\Pages;

use App\Filament\Clusters\Products\Resources\OtherIngredientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtherIngredients extends ListRecords
{
    protected static string $resource = OtherIngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
