<?php

namespace App\Filament\Resources\Shop\CustomerResource\RelationManagers;

use Akaunting\Money\Currency;
use App\Filament\Resources\Shop\OrderResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Payments');
    }

    public static function getModelLabel(): string
    {
        return __('Payment');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label(__('Order'))
                    ->relationship(
                        'order',
                        'number',
                        fn (Builder $query, RelationManager $livewire) => $query->whereBelongsTo($livewire->ownerRecord)
                    )
                    ->searchable()
                    ->hiddenOn('edit')
                    ->required(),

                Forms\Components\TextInput::make('reference')
                    ->label(__('Reference'))
                    ->columnSpan(fn (string $operation) => $operation === 'edit' ? 2 : 1)
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
                    ->numeric()
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->required(),

                Forms\Components\Select::make('currency')
                    ->label(__('Currency'))
                    ->options(collect(Currency::getCurrencies())->mapWithKeys(fn ($item, $key) => [$key => data_get($item, 'name')]))
                    ->searchable()
                    ->required(),

                Forms\Components\ToggleButtons::make('provider')
                    ->label(__('Provider'))
                    ->inline()
                    ->grouped()
                    ->options([
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                    ])
                    ->required(),

                Forms\Components\ToggleButtons::make('method')
                    ->label(__('Method'))
                    ->inline()
                    ->options([
                        'credit_card' => 'Credit card',
                        'bank_transfer' => 'Bank transfer',
                        'paypal' => 'PayPal',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.number')
                    ->label(__('Order'))
                    ->url(fn ($record) => OrderResource::getUrl('edit', [$record->order]))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ColumnGroup::make('Details')
                    ->label(__('Details'))
                    ->columns([
                        Tables\Columns\TextColumn::make('reference')
                            ->label(__('Reference'))
                            ->searchable(),

                        Tables\Columns\TextColumn::make('amount')
                            ->label(__('Monto'))
                            ->sortable()
                            ->money(fn ($record) => $record->currency),
                    ]),

                Tables\Columns\ColumnGroup::make('Context')
                    ->label(__('Context'))
                    ->columns([
                        Tables\Columns\TextColumn::make('provider')
                            ->label(__('Provider'))
                            ->formatStateUsing(fn ($state) => Str::headline($state))
                            ->sortable(),

                        Tables\Columns\TextColumn::make('method')
                            ->label(__('Method'))
                            ->formatStateUsing(fn ($state) => Str::headline($state))
                            ->sortable(),
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
