<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

use App\Filament\Resources\Shop\OrderResource;
use App\Models\Shop\Order;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(null);
    }

    protected function afterCreate(): void
    {
        /** @var Order $order */
        $order = $this->record;

        /** @var User $user */
        $user = auth()->user();

        Notification::make()
            ->title(__('New order'))
            ->icon('heroicon-o-shopping-bag')
            ->body("**{$order->customer?->name} ordenó productos.**")
            ->actions([
                Action::make('View')
                    ->label(__('View'))
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase($user);
    }

    /** @return Step[] */
    protected function getSteps(): array
    {
        return [
            Step::make(__('Order Details'))
                ->schema([
                    Section::make()->schema(OrderResource::getDetailsFormSchema())->columns(),
                ]),

            Step::make(__('Pizza'))
                ->schema([
                    Split::make([
                        Section::make()->schema([
                            Section::make()->schema(OrderResource::getTotal())->columns(),
                        ]),
                        Section::make()->schema([
                            OrderResource::getItemsRepeaterStar(),
                        ])->grow(false),
                    ])->from('md'),
                ]),

            Step::make(__('Order Items'))
                ->schema([
                    Section::make()->schema([
                        OrderResource::getItemsRepeater(),
                    ]),
                ]),

        ];
    }

    public static function afterSelectSpeciality($state, Forms\Get $get, Forms\Set $set): void
    {
        $getPrice = $get('size');
        $set('unit_price', $getPrice ? (Speciality::find($state)?->$getPrice ? Speciality::find($state)?->$getPrice * $get('quantity') : 0) : 0);
        $set('properties.ingredients', Speciality::find($state)?->ingredients->pluck('id')->toArray() ?? []);
        $set('placeholder_ingredients', Speciality::find($state)?->ingredients ? implode(', ', Speciality::find($state)?->ingredients->sortBy('name')->pluck('name')->toArray()) : '');
    }

    public static function afterSelectSpecialityHalf($state, $size, $anotherSpeciality, $setIngredient, $setPlaceholderIngredient, Forms\Set $set): void
    {
        $getPrice = $get('size');

        if ($getPrice && $anotherSpeciality) {
            $priceFirst = Speciality::find($anotherSpeciality)?->$getPrice ?? 0;
            if ($priceFirst > Speciality::find($state)?->$getPrice) {
                $priceSet = $priceFirst;
                $set('unit_price', $priceSet ?? 0);
            } else {
                // $set('unit_price', $getPrice ? (Speciality::find($state)?->$getPrice ?? 0) : 0);
                $priceSet = $getPrice ? (Speciality::find($state)?->$getPrice ?? 0) : 0;
            }
        } else {
            // $set('unit_price', $getPrice ? (Speciality::find($state)?->$getPrice ?? 0) : 0);
            $priceSet = $getPrice ? (Speciality::find($state)?->$getPrice ?? 0) : 0;
        }

        $set('unit_price', $getPrice ? $priceSet : 0);

        $set($setIngredient, Speciality::find($state)?->ingredients->pluck('id')->toArray() ?? []);
        $set($setPlaceholderIngredient, Speciality::find($state)?->ingredients ? implode(', ', Speciality::find($state)?->ingredients->sortBy('name')->pluck('name')->toArray()) : '');
    }

    public static function afterSelectIngredient($state, callable $set, $propertySpeciality, Forms\Get $get): void
    {
        $getPrice = $get('size');
        $speciality = $propertySpeciality ? Speciality::find($propertySpeciality) : null;
        $setPrice = $speciality?->$getPrice * $get('quantity');
        if ($speciality) {
            $storedIngredientIds = $speciality->find($propertySpeciality)->ingredients->pluck('id')->toArray();
            $providedIngredientIds = array_map('intval', $state); // Convierte a enteros
            $unstoredIngredientIds = array_diff($providedIngredientIds, $storedIngredientIds);

            $totalPrice = 0;

            foreach ($unstoredIngredientIds as $unstoredIngredient) {
                $ingredientUns = Ingredient::where('for_pizza', true)->find($unstoredIngredient);
                $priceIngredientUns = match ($getPrice) {
                    'price_small' => $ingredientUns?->price_small,
                    'price_medium' => $ingredientUns?->price_medium,
                    'price_large' => $ingredientUns?->price_large,
                    default => 0,
                };

                $totalPrice += ($priceIngredientUns * $get('quantity'));
            }
        }
        $setPrice += $totalPrice;
        $set('unit_price', $getPrice ? $setPrice : 0);

    }

    public static function resetIngredients($state, callable $set, Forms\Get $get): void
    {
        $set('properties.ingredients', $get('properties.speciality_id') ? Speciality::find($get('properties.speciality_id'))?->ingredients->pluck('id')->toArray() ?? [] : []);
        $set('properties.ingredients_second', $get('properties.speciality_id_second') ? Speciality::find($get('properties.speciality_id_second'))?->ingredients->pluck('id')->toArray() ?? [] : []);
    }
}
