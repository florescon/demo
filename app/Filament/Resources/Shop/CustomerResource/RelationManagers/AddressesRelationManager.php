<?php

namespace App\Filament\Resources\Shop\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Squire\Models\Country;
use Illuminate\Database\Eloquent\Model;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'full_address';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Addresses');
    }    

    public static function getModelLabel(): string
    {
        return __('Address');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('street')
                ->label(__('Street'))
                ->minLength(3)
                ->maxLength(100)
                ->required()
                ,

                Forms\Components\TextInput::make('num')
                ->label(__('Number'))
                ->integer()
                ->minLength(1)
                ->maxLength(10)
                ->required()
                ,

                Forms\Components\TextInput::make('Departament')
                ->label(__('Departament'))
                ->minLength(3)
                ->maxLength(100)
                ->required()
                ,

                Forms\Components\TextInput::make('zip')
                ->length(5)
                ->integer()
                ->required()
                ->label(__('CP'))
                ,

                Forms\Components\TextInput::make('city')
                ->label(__('City'))
                ->default('Lagos de Moreno')
                ->readOnly()
                ,

                Forms\Components\TextInput::make('state')
                ->label(__('State'))
                ->default('Jalisco')
                ->readOnly()
                ,

                Forms\Components\Select::make('country')
                    ->label(__('Country'))
                    ->searchable()
                    ->default('mx')
                    // ->getSearchResultsUsing(fn (string $query) => Country::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                    ->getOptionLabelUsing(fn ($value): ?string => Country::firstWhere('id', $value)?->getAttribute('name'))
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('num')
                ->label(__('Number'))
                ,

                Tables\Columns\TextColumn::make('street')
                ->label(__('Street'))
                ,

                Tables\Columns\TextColumn::make('departament')
                ->label(__('Departament'))
                ,

                Tables\Columns\TextColumn::make('zip')
                ->label(__('CP'))
                ,

                // Tables\Columns\TextColumn::make('country')
                //     ->label(__('Country'))
                //     ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DetachBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
