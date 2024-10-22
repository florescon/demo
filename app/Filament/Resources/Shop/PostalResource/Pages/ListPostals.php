<?php

namespace App\Filament\Resources\Shop\PostalResource\Pages;

use App\Filament\Resources\Shop\PostalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostals extends ListRecords
{
    protected static string $resource = PostalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
