<?php

namespace App\Filament\Clusters\Products\Resources;

use App\Filament\Clusters\Products;
use App\Filament\Clusters\Products\Resources\OtherIngredientResource\Pages;
use App\Models\Shop\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OtherIngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Products::class;

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Ingredients');
    }

    public static function getModelLabel(): string
    {
        return __('Ingredient');
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

                        Forms\Components\Grid::make()
                            ->schema([

                                Forms\Components\TextInput::make('price')
                                    ->label(__('Price'))
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        $set('price_medium', $state);
                                        $set('price_large', $state);
                                    }),

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
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->searchable()
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
                return $query->where('for_pizza', false);
            })
            ->defaultSort('sort')
            ->defaultPaginationPageOption(10)
            ->reorderable('sort')
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
            'index' => Pages\ListOtherIngredients::route('/'),
            'create' => Pages\CreateOtherIngredient::route('/create'),
            'edit' => Pages\EditOtherIngredient::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('for_pizza', false)->count();
    }
}
