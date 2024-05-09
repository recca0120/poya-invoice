<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

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
            ->modifyQueryUsing(fn (Builder $query) => $query->with('eventWinners.user'))
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('quantity')->numeric(),
                Tables\Columns\TextColumn::make('eventWinners')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->bulleted()
                    ->html()
                    ->getStateUsing(function (EventPrize $record) {
                        return $record->eventWinners
                            ->map(function (EventWinner $eventWinner) {
                                $user = $eventWinner->user;
                                $lookup = [
                                    '姓名' => $user->name,
                                    '會員卡號' => $user->member_card_number,
                                    '電話號碼' => $user->phone_number,
                                ];

                                return implode('<br />', array_reduce(
                                    array_keys($lookup),
                                    static function (array $carry, string $key) use ($lookup) {
                                        $value = $lookup[$key];

                                        return $value ? [...$carry, $key.': '.$value] : $carry;
                                    },
                                    []
                                ));
                            });
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
