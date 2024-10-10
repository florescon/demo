<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

use App\Filament\Resources\Shop\OrderResource;
use App\Models\Shop\Order;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Forms\Components\Split;

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
            ->body("**{$order->customer?->name} ordenó {$order->theitems->count()} productos.**")
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
                            Section::make()->schema(OrderResource::getTotal())->columns()
                        ]),
                        Section::make()->schema([
                            OrderResource::getItemsRepeaterStar(),
                        ])->grow(false),
                    ])->from('md')
                ]),

            Step::make(__('Order Items'))
                ->schema([
                    Section::make()->schema([
                        OrderResource::getItemsRepeater(),
                    ]),
                ]),

        ];
    }
}
