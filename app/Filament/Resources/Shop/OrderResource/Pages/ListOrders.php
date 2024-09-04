<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

use App\Filament\Resources\Shop\OrderResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make(__('All')),
            __('New') => Tab::make()->query(fn ($query) => $query->where('status', 'new')),
            __('Processing') => Tab::make()->query(fn ($query) => $query->where('status', 'processing')),
            __('Shipped') => Tab::make()->query(fn ($query) => $query->where('status', 'shipped')),
            __('Delivered') => Tab::make()->query(fn ($query) => $query->where('status', 'delivered')),
            __('Cancelled') => Tab::make()->query(fn ($query) => $query->where('status', 'cancelled')),
        ];
    }
}
