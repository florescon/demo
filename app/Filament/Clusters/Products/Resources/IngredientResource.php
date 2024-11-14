<?php

namespace App\Filament\Clusters\Products\Resources;

use App\Filament\Clusters\Products;
use App\Filament\Clusters\Products\Resources\IngredientResource\Pages;
use App\Models\Shop\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Products::class;

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Pizza Ingredients');
    }

    public static function getModelLabel(): string
    {
        return __('Pizza Ingredient');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(50)
                                    ->live(onBlur: true),
                            ]),
                        Forms\Components\Hidden::make('for_pizza')->default(true),

                        Forms\Components\Grid::make()
                            ->schema([

                                Forms\Components\TextInput::make('price_small')
                                    ->label(__('Small Price'))
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        $set('price_medium', $state);
                                        $set('price_large', $state);
                                    }),

                                Forms\Components\TextInput::make('price_medium')
                                    ->label(__('Medium Price'))
                                    ->numeric()
                                    ->gte('price_small')
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),

                                Forms\Components\TextInput::make('price_large')
                                    ->label(__('Large Price'))
                                    ->numeric()
                                    ->gte('price_medium')
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),
                            ]),

                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('Visible to customers.'))
                            ->default(true),

                        Forms\Components\MarkdownEditor::make('description')
                            ->label(__('Description')),
                    ])
                    ->columnSpan(['lg' => fn (?Ingredient $record) => $record === null ? 3 : 2]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('position')
                            ->label(__('Position'))
                            ->numeric()
                            ->rules(['regex:/^\d{1,4}(\.\d{0,2})?$/'])
                            ->helperText(__('This product will be according to position.'))
                            ->default(0),

                        Forms\Components\Placeholder::make('created_at')
                            ->label(__('Created at'))
                            ->content(fn (Ingredient $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label(__('Last modified at'))
                            ->content(fn (Ingredient $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Ingredient $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('Position'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_small')
                    ->label(__('Small Price'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_medium')
                    ->label(__('Medium Price'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_large')
                    ->label(__('Large Price'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('Visibility'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated Date'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('for_pizza', true);
            })
            ->defaultSort('position')
            ->defaultPaginationPageOption(10)
            ->reorderable('position')
            ->selectCurrentPageOnly();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('for_pizza', true)->count();
    }
}
