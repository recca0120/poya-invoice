<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Exports\EventWinnerExporter;
use App\Filament\Tables\Actions\DrawAction;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\User;
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with('winners'))
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
                        return $record->winners
                            ->map(function (User $user) {
                                $lookup = [
                                    '姓名' => $user->name,
                                    '會員卡號' => $user->member_code,
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
                DrawAction::make(),
                Tables\Actions\ExportAction::make()
                    ->exporter(EventWinnerExporter::class)
                    ->modifyQueryUsing(function (Builder $query) {
                        return $query
                            ->select('*')
                            ->selectRaw('event_winner.user_id AS winner_id')
                            ->with('winner')
                            ->join(
                                'event_winner',
                                'event_prizes.id',
                                '=',
                                'event_winner.event_prize_id'
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
            ->defaultSort('id');
    }
}
