<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\ChefResource\Pages;
use App\Filament\Resources\Shop\ChefResource\RelationManagers;
use App\Models\Shop\Chef;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChefResource extends Resource
{
    protected static ?string $model = Chef::class;

    protected static ?string $slug = 'shop/chefs';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-m-building-office';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Chefs');
    }

    public static function getModelLabel(): string
    {
        return __('Chef');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Select::make('branch_id')
                ->label(__('Branch'))
                ->relationship('branch', 'name')
                ->searchable(['name'])
                ->required()
                ->optionsLimit(5)
                ->live()
                ->dehydrated(false),
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->maxLength(255)
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
                Tables\Columns\TextColumn::make('branch.name')
                    ->label(__('Branch'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListChefs::route('/'),
            'create' => Pages\CreateChef::route('/create'),
            'edit' => Pages\EditChef::route('/{record}/edit'),
        ];
    }
}
