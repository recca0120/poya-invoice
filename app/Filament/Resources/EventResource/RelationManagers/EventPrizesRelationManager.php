<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Exports\EventWinnerExporter;
use App\Filament\Tables\Actions\DrawAction;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventWinner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method Event getOwnerRecord()
 */
class EventPrizesRelationManager extends RelationManager
{
    protected $listeners = ['refreshRelation' => '$refresh'];

    protected static bool $isLazy = false;

    protected static string $relationship = 'eventPrizes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->rules(['numeric', 'min:1'])
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('eventWinners.eventUser.user'))
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('quantity')->numeric(),
                Tables\Columns\TextColumn::make('winners')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->bulleted()
                    ->html()
                    ->getStateUsing(function (EventPrize $record) {
                        return $record->eventWinners->map(function (EventWinner $eventWinner) {
                            return $eventWinner->eventUser->user->toWinnerString();
                        });
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                DrawAction::make(),
                Tables\Actions\ExportAction::make()
                    ->label('匯出中獎名單')
                    ->color('info')
                    ->exporter(EventWinnerExporter::class)
                    ->visible($this->getOwnerRecord()->drawn)
                    ->modifyQueryUsing(function (Builder $query) {
                        return $query->whereRelation(
                            'eventUser',
                            'event_id',
                            '=',
                            $this->getOwnerRecord()->id
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('event_prizes.id');
    }
}
