<?php

namespace App\Filament\Clusters\Products\Resources\ProductResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Comments');
    }

    public static function getModelLabel(): string
    {
        return __('Comment');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->required(),

                Forms\Components\Select::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Toggle::make('is_visible')
                    ->label(__('Approved for public'))
                    ->default(true),

                Forms\Components\MarkdownEditor::make('content')
                    ->required()
                    ->label(__('Content')),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                TextEntry::make('title'),
                TextEntry::make('customer.name'),
                IconEntry::make('is_visible')
                    ->label('Visibility'),
                TextEntry::make('content')
                    ->markdown(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('Visibility'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record) {
                        /** @var User $user */
                        $user = auth()->user();

                        Notification::make()
                            ->title('New comment')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->body("**{$record->customer->name} commented on product ({$record->commentable->name}).**")
                            ->sendToDatabase($user);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
