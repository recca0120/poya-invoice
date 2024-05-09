<?php

namespace App\Filament\Actions;

use App\Enums\YesOrNo;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventWinner;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class DrawAction extends Action
{
    use CanCustomizeProcess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('抽獎')
            ->icon('heroicon-o-gift')
            ->color(Color::Teal)
            ->visible(fn (Event $record) => $record->ended)
            ->form([
                ToggleButtons::make('repeat')
                    ->label('同會員可重複中獎')
                    ->options(YesOrNo::class)
                    ->grouped()
                    ->inlineLabel(false)
                    ->default(YesOrNo::NO->value),
            ])
            ->requiresConfirmation(static fn (Event $record) => $record->drawn === true)
            ->action(function (array $data) {
                $this->process(function (Event $record) use ($data) {
                    $repeat = YesOrNo::from($data['repeat']);

                    $users = $record
                        ->eventUsers()
                        ->where('approved', true)
                        ->pluck('user_id');

                    $prizes = $record->eventPrizes
                        ->map(fn (EventPrize $prize) => collect(range(1, $prize->quantity))->map(fn () => new Fluent([
                            'event_prize_id' => $prize->id, 'user_id' => null,
                        ])))
                        ->collapse()
                        ->shuffle();

                    EventWinner::query()->toBase()->where('event_id', $record->id)->truncate();
                    while ($users->isNotEmpty()) {
                        $drawablePrizes = $prizes->reject(fn (Fluent $prize) => $prize->user_id);
                        /** @var Fluent $prize */
                        $prize = $drawablePrizes->first();

                        if (! $prize) {
                            break;
                        }

                        $drawnUsers = $prizes->filter(fn (Fluent $tmp) => $tmp->id === $prize->id)->toArray();
                        $prize->user_id = $users
                            ->reject(fn (int $id) => in_array($id, $drawnUsers, true))
                            ->random();

                        if ($repeat === YesOrNo::NO) {
                            $users = $users->reject($prize->user_id);
                        }
                    }
                    DB::beginTransaction();
                    $prizes
                        ->reject(fn (Fluent $prize) => ! $prize->user_id)
                        ->unique(fn (Fluent $prize) => [$prize->event_prize_id, $prize->user_id])
                        ->each(function (Fluent $prize) use ($record) {
                            return EventWinner::create([
                                'event_id' => $record->id,
                                'event_prize_id' => $prize->event_prize_id,
                                'user_id' => $prize->user_id,
                            ]);
                        });
                    DB::commit();
                });

                $this->success();
            });
    }
}
