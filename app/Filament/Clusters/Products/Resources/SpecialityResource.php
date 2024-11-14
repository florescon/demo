<?php

namespace App\Filament\Clusters\Products\Resources;

use App\Filament\Clusters\Products;
use App\Filament\Clusters\Products\Resources\SpecialityResource\Pages;
use App\Models\Shop\Ingredient;
use App\Models\Shop\Speciality;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpecialityResource extends Resource
{
    protected static ?string $model = Speciality::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Products::class;

    protected static ?string $navigationParentItem = 'Products';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('Pizza Specialties');
    }

    public static function getModelLabel(): string
    {
        return __('Pizza Speciality');
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

                        Forms\Components\Grid::make()
                            ->schema([

                                Forms\Components\TextInput::make('price_small')
                                    ->label(__('Small Price'))
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),

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

                        Forms\Components\MarkdownEditor::make('notes')
                            ->label(__('Note')),
                    ])
                    ->columnSpan(['lg' => fn (?Speciality $record) => $record === null ? 3 : 2]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label(__('Created at'))
                            ->content(fn (Speciality $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label(__('Last modified at'))
                            ->content(fn (Speciality $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Speciality $record) => $record === null),
                Forms\Components\CheckboxList::make('ingredients')
                    ->label(__('Ingredients'))
                    ->relationship('ingredients', 'name') // Configura el campo de la relación
                    ->options(Ingredient::all()->pluck('name', 'id')) // Obtén las opciones de la base de datos
                    ->searchable()
                    ->columns(2), // Opcional: número de columnas para mostrar las casillas
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

                Tables\Columns\TextColumn::make('ingredients.name')
                    ->label(__('Ingredients'))
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->searchable()
                    ->expandableLimitedList(),

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

                Tables\Columns\TextColumn::make('notes')
                    ->label(__('Notes'))
                    ->searchable()
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
            ->defaultPaginationPageOption(10)
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
            'index' => Pages\ListSpecialities::route('/'),
            'create' => Pages\CreateSpeciality::route('/create'),
            'edit' => Pages\EditSpeciality::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::count();
    }
}
