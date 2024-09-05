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
                ,

                Forms\Components\TextInput::make('zip')
                ->label(__('CP'))
                ,

                Forms\Components\TextInput::make('city')
                ->label(__('City'))
                ,

                Forms\Components\TextInput::make('state')
                ->label(__('State'))
                ,

                Forms\Components\Select::make('country')
                    ->label(__('Country'))
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $query) => Country::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                    ->getOptionLabelUsing(fn ($value): ?string => Country::firstWhere('id', $value)?->getAttribute('name')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('street')
                ->label(__('Street'))
                ,

                Tables\Columns\TextColumn::make('zip')
                ->label(__('CP'))
                ,

                Tables\Columns\TextColumn::make('city')
                ->label(__('City'))
                ,

                Tables\Columns\TextColumn::make('country')
                    ->label(__('Country'))
                    ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
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
