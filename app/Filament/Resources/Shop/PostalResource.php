<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\PostalResource\Pages;
use App\Filament\Resources\Shop\PostalResource\RelationManagers;
use App\Models\Shop\Postal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PostalResource extends Resource
{
    protected static ?string $model = Postal::class;

    protected static ?string $slug = 'shop/postals';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-m-building-office';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Postal Codes');
    }

    public static function getModelLabel(): string
    {
        return __('Postal Code');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('Postal Code'))
                    ->integer()
                    ->length(5)
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\Select::make('settlement')
                    ->label(__('Settlement'))
                    ->options([
                        'Colonia' => 'Colonia',
                        'Fraccionamiento' => 'Fraccionamiento',
                        'Rancho' => 'Rancho',
                        'Ranchería' => 'Ranchería',
                        'Barrio' => 'Barrio',
                        'Hacienda' => 'Hacienda',
                        'Ejido' => 'Ejido',
                        'Zona Industrial' => 'Zona industrial',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label(__('Price'))
                    ->integer()
                    ->rules(['regex:/^\d{1,3}(\.\d{0,2})?$/'])
                    ->required(),

                Forms\Components\Toggle::make('active')
                    ->label(__('Active'))
                    ->default(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('Postal Code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('settlement')
                    ->label(__('Settlement'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('Active'))
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('Activate'))
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records) {
                            $records->each(function ($record) {
                                $record->update(['active' => true]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('Deactivate'))
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records) {
                            $records->each(function ($record) {
                                $record->update(['active' => false]);
                            });
                        }),
                ]),
            ])
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
            'index' => Pages\ListPostals::route('/'),
            'create' => Pages\CreatePostal::route('/create'),
            'edit' => Pages\EditPostal::route('/{record}/edit'),
        ];
    }
}
