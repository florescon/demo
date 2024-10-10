<?php

namespace App\Filament\Resources\Shop;

use App\Enums\OrderStatus;
use App\Enums\OrderPriority;
use App\Enums\PropertyType;
use App\Filament\Clusters\Products\Resources\ProductResource;
use App\Filament\Resources\Shop\OrderResource\Pages;
use App\Filament\Resources\Shop\OrderResource\RelationManagers;
use App\Filament\Resources\Shop\OrderResource\Widgets\OrderStats;
use App\Forms\Components\AddressForm;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\Shop\Speciality;
use App\Models\Shop\Ingredient;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Squire\Models\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Filament\Support\Enums\Alignment;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'shop/orders';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Orders');
    }

    public static function getModelLabel(): string
    {
        return __('Order');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make(__('Order items'))
                            ->headerActions([
                                Action::make('pdf') 
                                    ->label('PDF')
                                    ->color('success')
                                    ->action(function (Model $record) {
                                        return response()->streamDownload(function () use ($record) {
                                            echo Pdf::loadHtml(
                                                Blade::render('pdf', ['record' => $record])
                                            )->setPaper([0, 0, 1385.98, 296.85], 'landscape')->stream();
                                        }, $record->number . '.pdf');
                                    }), 

                            ])
                            ->footerActions([
                                Action::make('reset')
                                    ->modalHeading('Are you sure?')
                                    ->modalDescription('All existing items will be removed from the order.')
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn (Forms\Set $set) => $set('items', [])),
                            ])
                            ->footerActionsAlignment(Alignment::End)
                            ->schema([
                                static::getItemsRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('total_order')
                            ->label(__('Total'))
                            ->content(fn (Order $record): ?string => '$' . $record->total_order),

                        Forms\Components\TextInput::make('shipping_price')
                            ->label(__('Shipping'))
                            ->integer()
                            ->helperText(__('This value adds to the total.'))
                            ->default(0),

                        Forms\Components\Placeholder::make('created_at_time')
                            ->label(__('Hour'))
                            ->content(fn (Order $record): ?string => $record->created_at_time),

                        Forms\Components\Placeholder::make('created_at')
                            ->label(__('Created at'))
                            ->content(fn (Order $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label(__('Last modified at'))
                            ->content(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Order $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('Number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at_time')
                    ->label(__('Hour')),
                Tables\Columns\TextColumn::make('total_order')
                    ->label(__('Total')),
                Tables\Columns\TextColumn::make('shipping_price')
                    ->label(__('Shipping cost'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('MXN'),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Order Date'))
                    ->date()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('Created from'))
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('Created until'))
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('pdf') 
                    ->label('PDF')
                    ->color('success')
                    ->action(function (Model $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo Pdf::loadHtml(
                                Blade::render('pdf', ['record' => $record])
                            )->setPaper([0, 0, 1385.98, 296.85], 'landscape')->stream();
                        }, $record->number . '.pdf');
                    }), 
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label(__('Created'))
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Order> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */

        return [
            __('Customer') => optional($record->customer)->name,
        ];
    }

    /** @return Builder<Order> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'items']);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 'new')->count();
    }

    /** @return Forms\Components\Component[] */
    public static function getDetailsFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('number')
                ->label(__('Number'))
                ->default('OR-' . now()->format('dmy-Hi') .'-'. random_int(1000, 9999))
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(32)
                ->unique(Order::class, 'number', ignoreRecord: true),

            Forms\Components\Select::make('shop_customer_id')
                ->label(__('Customer'))
                ->relationship('customer', 'name')
                ->searchable(['name', 'phone'])
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label(__('Email address'))
                        ->required()
                        ->email()
                        ->maxLength(255)
                        ->unique(),

                    Forms\Components\TextInput::make('phone')
                        ->label(__('Phone'))
                        ->maxLength(255),

                    Forms\Components\Select::make('gender')
                        ->label(__('Gender'))
                        ->placeholder(__('Select gender'))
                        ->options([
                            'male' => __('Male'),
                            'female' => __('Female'),
                        ])
                        ->required()
                        ->native(false),
                ])
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading(__('Create customer'))
                        ->modalSubmitActionLabel(__('Create customer'))
                        ->modalWidth('lg');
                }),

            Forms\Components\ToggleButtons::make('status')
                ->label(__('Status'))
                ->inline()
                ->default('processing')
                ->options(OrderStatus::class)
                ->required(),

            Forms\Components\Select::make('currency')
                ->label(__('Moneda'))
                ->searchable()
                ->default('mxn')
                // ->getSearchResultsUsing(fn (string $query) => Currency::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                ->getOptionLabelUsing(fn ($value): ?string => Currency::firstWhere('id', $value)?->getAttribute('name'))
                ->required(),

            Forms\Components\ToggleButtons::make('priority')
                ->label(__('Priority'))
                ->inline()
                ->default('medium')
                ->options(OrderPriority::class)
                ->required(),

            // AddressForm::make('address')
            //     ->columnSpan('full'),

            // Forms\Components\MarkdownEditor::make('notes')
            //     ->label(__('Notes'))
            //     ->columnSpan('full'),
        ];
    }

    public static function getItemsRepeaterStar(): Repeater
    {
        return Repeater::make('pizzas')
            ->label(__('Items'))
            ->relationship()
            ->columnSpan(2)
            ->columns()
            ->schema([
                Forms\Components\Placeholder::make('Pizza')
                    ->extraAttributes([
                        'style'=>'border: 3px solid orange; font-weight: bold; text-center; text-align:center;'
                    ])
                    ->label(__('Content'))
                    ->content('Pizza'),

                Forms\Components\TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->required()
                    ->rules(['min:1', 'not_in:0'])
                    ->default(1),

                Forms\Components\ToggleButtons::make('size')
                    ->label(__('Size'))
                    ->inline()
                    ->required()
                    ->options([
                        'price_small' => __('Small'),
                        'price_medium' => __('Medium'),
                        'price_large' => __('Large')
                    ]),
                Forms\Components\ToggleButtons::make('choose')
                    ->label(__('Choose'))
                    ->inline()
                    ->live()
                    ->required()
                    ->options([
                        'half' => __('Half'),
                        'complete' => __('Complete')
                    ])
                    ->colors([
                            'half' => 'info',
                            'complete' => 'success',
                        ])
                    ,

                Forms\Components\Fieldset::make('properties.speciality')
                    ->label(__('Specialties'))
                    ->extraAttributes([
                        'style'=>'border: 4px solid green;'
                    ])
                    ->visible(function (Forms\Get $get) { 
                        return $get('choose') == 'complete'; 
                    })
                    ->schema([
                        Forms\Components\Select::make('properties.speciality_id')
                            ->suffixIcon('heroicon-m-beaker')
                            ->suffixIconColor('success')
                            ->required()
                            ->label(__('Speciality'))
                            ->visible(function (Forms\Get $get) { 
                                return $get('choose') == 'complete'; 
                            })
                            ->helperText(__('Select the Speciality'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $query) => Speciality::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                            ->getOptionLabelUsing(fn ($value): ?string => Speciality::find($value)?->name)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, Forms\Get $get) {
                                // Actualiza los ingredientes cuando la especialidad cambia

                                $getPrice = $get('size');
                                $set('unit_price', $getPrice ? (Speciality::find($state)?->$getPrice ?? 0) : 0);
                                $set('properties.ingredients', Speciality::find($state)?->ingredients->pluck('id')->toArray() ?? []);
                                $set('placeholder_ingredients', Speciality::find($state)?->ingredients ? implode(', ', Speciality::find($state)?->ingredients->sortBy('name')->pluck('name')->toArray()) : '');
                            }),

                        Forms\Components\Placeholder::make('placeholder_ingredients')
                            ->label(__('Ingredients by default'))
                            ->content(function (callable $get) {
                                // Aquí se obtienen todos los ingredientes
                                return  $get('placeholder_ingredients') ? '-> '. $get('placeholder_ingredients') .' <-' : '<- '. __('Select the Specialty');
                            }),

                        Forms\Components\TextInput::make('unit_price')
                            ->label(__('Unit Price'))
                            ->readOnly()
                            // ->dehydrated()
                            ->numeric()
                            ->required()
                            ->columns(2)
                            ->columnSpanFull(),

                        Forms\Components\CheckboxList::make('properties.ingredients')
                            ->label(__('Ingredients'))
                            ->columnSpanFull()
                            ->columns(2)
                            ->options(function (callable $get) {
                                // Aquí se obtienen todos los ingredientes
                                return Ingredient::orderBy('name')->pluck('name', 'id')->toArray();
                            })
                            ->visible(function (callable $get) {
                                return $get('properties.speciality_id') !== null;
                            })
                            ->searchable()
                            ->noSearchResultsMessage('No ingredients found.'),

                        // Forms\Components\Select::make('extra_ingredients')
                            // ->label(__('Extra Ingredients')),
                    ]),

                Forms\Components\Fieldset::make('properties.speciality')
                    ->label(__('Specialties by halves'))
                    ->extraAttributes([
                        'style'=>'border: 4px solid red;'
                    ])
                    ->visible(function (Forms\Get $get) { 
                        return $get('choose') == 'half'; 
                    })
                    ->schema([
                        Forms\Components\Placeholder::make('Primer mitad')
                            ->extraAttributes([
                                'style'=>'border-bottom: 4px solid black;'
                            ])
                            ->content(function (Forms\Get $get): string {
                                return '1';
                            })
                            ->columnSpanFull(),

                        Forms\Components\Select::make('properties.speciality_id')
                            ->suffixIcon('heroicon-m-beaker')
                            ->suffixIconColor('success')
                            ->label(__('Speciality'))
                            ->visible(function (Forms\Get $get) { 
                                return $get('choose') == 'half'; 
                            })
                            ->helperText(__('Select the Speciality'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $query) => Speciality::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                            ->getOptionLabelUsing(fn ($value): ?string => Speciality::find($value)?->name)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Actualiza los ingredientes cuando la especialidad cambia

                                $set('properties.ingredients', Speciality::find($state)?->ingredients->pluck('id')->toArray() ?? []);
                                $set('placeholder_ingredients', Speciality::find($state)?->ingredients ? implode(', ', Speciality::find($state)?->ingredients->sortBy('name')->pluck('name')->toArray()) : '');
                            }),

                        Forms\Components\Placeholder::make('placeholder_ingredients')
                            ->label(__('Ingredients by default'))
                            ->content(function (callable $get) {
                                // Aquí se obtienen todos los ingredientes
                                return  $get('placeholder_ingredients') ? '-> '. $get('placeholder_ingredients') .' <-' : '<- '. __('Select the Specialty');
                            }),

                        Forms\Components\CheckboxList::make('properties.ingredients')
                            ->label(__('Ingredients'))
                            ->columnSpanFull()
                            ->columns(2)
                            ->options(function (callable $get) {
                                // Aquí se obtienen todos los ingredientes
                                return Ingredient::orderBy('name')->pluck('name', 'id')->toArray();
                            })
                            ->visible(function (callable $get) {
                                return $get('properties.speciality_id') !== null;
                            })
                            ->searchable()
                            ->noSearchResultsMessage('No ingredients found.'),






                        Forms\Components\Placeholder::make('Segunda mitad')
                            ->extraAttributes([
                                'style'=>'border-bottom: 4px solid black;'
                            ])
                            ->content(function (Forms\Get $get): string {
                                return '2';
                            })
                            ->columnSpanFull(),

                        Forms\Components\Select::make('properties.speciality_id_second')
                            ->suffixIcon('heroicon-m-beaker')
                            ->suffixIconColor('success')
                            ->label(__('Speciality'))
                            ->visible(function (Forms\Get $get) { 
                                return $get('choose') == 'half'; 
                            })
                            ->required()
                            ->helperText(__('Select the Speciality'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $query) => Speciality::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                            ->getOptionLabelUsing(fn ($value): ?string => Speciality::find($value)?->name)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Actualiza los ingredientes cuando la especialidad cambia

                                $set('properties.ingredients_second', Speciality::find($state)?->ingredients->pluck('id')->toArray() ?? []);
                                $set('placeholder_ingredients_second', Speciality::find($state)?->ingredients ? implode(', ', Speciality::find($state)?->ingredients->sortBy('name')->pluck('name')->toArray()) : '');
                            }),

                        Forms\Components\Placeholder::make('placeholder_ingredients_second')
                            ->label(__('Ingredients by default'))
                            ->content(function (callable $get) {
                                // Aquí se obtienen todos los ingredientes
                                return  $get('placeholder_ingredients_second') ? '-> '. $get('placeholder_ingredients_second') .' <-' : '<- '. __('Select the Specialty');
                            }),

                        Forms\Components\CheckboxList::make('properties.ingredients_second')
                            ->label(__('Ingredients'))
                            ->columnSpanFull()
                            ->columns(2)
                            ->options(function (callable $get) {
                                // Aquí se obtienen todos los ingredientes
                                return Ingredient::orderBy('name')->pluck('name', 'id')->toArray();
                            })
                            ->visible(function (callable $get) {
                                return $get('properties.speciality_id_second') !== null;
                            })
                            ->searchable()
                            ->noSearchResultsMessage('No ingredients found.'),





                        // Forms\Components\Select::make('extra_ingredients')
                            // ->label(__('Extra Ingredients')),
                    ]),

        ])
        ->live()
        ->reorderable(false)
        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
            self::updateTotals($get, $set);
        })
        ->deleteAction(
            fn (Action $action) => $action->requiresConfirmation(),
            fn(Action $action) => $action->after(fn(Forms\Get $get, Forms\Set $set) => self::updateTotals($get, $set)),
        );
    }

    /** @return Forms\Components\Component[] */
    public static function getTotal(): array
    {
        return [
            Forms\Components\Placeholder::make('time')
                ->content(fn ($state): string =>  now()->format('H:i'))
                ->columnSpanFull(),

            Forms\Components\TextInput::make('subtotal')
                ->label(__('Total'))
                ->numeric()
                // Read-only, because it's calculated
                ->readOnly()
                ->columnSpanFull()
                ->prefix('$')
                // This enables us to display the subtotal on the edit page load
                ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set) {
                    self::updateTotals($get, $set);
                }),
        ];
    }

    // This function updates totals based on the selected products and quantities
    public static function updateTotals(Forms\Get $get, Forms\Set $set): void
    {

        // dd($get('pizzas'));
        // Retrieve all selected products and remove empty rows
        $selectedSpecialties = collect($get('pizzas'))->filter(fn($item) => !empty($item['properties']['speciality_id']) && !empty($item['quantity']) && !empty($item['size']));

        // Retrieve prices for all selected products
        // $prices = Speciality::find($selectedSpecialties->pluck('properties.speciality_id'))->pluck('price_small', 'id');

        // Retrieve prices for all selected products
        $specialityIds = $selectedSpecialties->pluck('properties.speciality_id')->unique();
        $specialities = Speciality::find($specialityIds);

        // Prepare an array to store the total prices
        $prices = [];

        foreach ($selectedSpecialties as $item) {

            $storedIngredientIds = $specialities->find($item['properties']['speciality_id'])->ingredients->pluck('id')->toArray();
            $providedIngredientIds = array_map('intval', $item['properties']['ingredients']); // Convierte a enteros
            $unstoredIngredientIds = array_diff($providedIngredientIds, $storedIngredientIds);

            $totalPrice = 0;

            foreach($unstoredIngredientIds as $unstoredIngredient)
            {
                $ingredientUns = Ingredient::find($unstoredIngredient);
                // $priceIngredientUns = $ingredientUns->price;

                // Determine the correct price based on size
                $priceIngredientUns = match ($item['size']) {
                    'price_small' => $ingredientUns?->price_small,
                    'price_medium' => $ingredientUns?->price_medium,
                    'price_large' => $ingredientUns?->price_large,
                    default => 0,
                };

                $totalPrice += $priceIngredientUns;
            }
            // dd($totalPrice);

            $speciality = $specialities->find($item['properties']['speciality_id']);

            // Determine the correct price based on size
            $price = match ($item['size']) {
                'price_small' => $speciality?->price_small,
                'price_medium' => $speciality?->price_medium,
                'price_large' => $speciality?->price_large,
                default => 0,
            };

            // Calculate the total price for this item
            $totalPrice += $price * $item['quantity'];

            // Add or update the total price for this speciality
            if (isset($prices[$item['properties']['speciality_id']])) {
                $prices[$item['properties']['speciality_id']] += $totalPrice;
            } else {
                $prices[$item['properties']['speciality_id']] = $totalPrice;
            }
        }
     

        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedSpecialties->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['properties']['speciality_id']] * $product['quantity']);
        }, 0);
     
        // Update the state with the new values
        $set('subtotal', number_format($subtotal, 2, '.', ''));
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->label(__('Items'))
            ->relationship()
            ->schema([
                Forms\Components\Select::make('shop_product_id')
                    ->label(__('Product'))
                    ->options(Product::query()->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('unit_price', Product::find($state)?->price ?? 0))
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('qty')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->default(1)
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->required(),

                Forms\Components\TextInput::make('unit_price')
                    ->label(__('Unit Price'))
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 3,
                    ]),
            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip(__('Open product'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);

                        $product = Product::find($itemData['shop_product_id']);

                        if (! $product) {
                            return null;
                        }

                        return ProductResource::getUrl('edit', ['record' => $product]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['shop_product_id'])),
            ])
            ->orderColumn('sort')
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->required();
    }
}
