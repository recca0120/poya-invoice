<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Imports\EventUserImporter;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventUserRelationManager extends RelationManager
{
    protected static string $relationship = 'eventUser';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('會員')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('sn')
                    ->label(value(static fn (Event $event) => $event->type->getColumnName(), $this->getOwnerRecord()))
                    ->required(),
                Forms\Components\Toggle::make('approved')->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['event', 'user']))
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('event.code')->label('活動代號'),
                Tables\Columns\TextColumn::make('user.name')->label('姓名'),
                Tables\Columns\TextColumn::make('sn')
                    ->label(value(static fn (Event $event) => $event->type->getColumnName(), $this->getOwnerRecord())),
                Tables\Columns\ToggleColumn::make('approved'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\ImportAction::make()
                    ->importer(EventUserImporter::class)
                    ->options([
                        'sn_label' => $this->getOwnerRecord()->type->getColumnName(),
                        'event_id' => $this->getOwnerRecord()->id,
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}