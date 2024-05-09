<?php

namespace App\Filament\Actions;

use App\Enums\YesOrNo;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventWinner;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class DrawAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('抽獎')
            ->icon('heroicon-o-gift')
            ->color(Color::Teal)
            ->visible(fn (Event $record) => $record->isEnd())
            ->form([
                ToggleButtons::make('repeat')
                    ->label('同會員可重複中獎')
                    ->options(YesOrNo::class)
                    ->grouped()
                    ->inlineLabel(false)
                    ->default(YesOrNo::NO->value),
            ])
            ->action(function (Event $record, array $data) {
                $repeat = YesOrNo::from($data['repeat']);

                $users = $record
                    ->eventUsers()
                    ->where('approved', true)
                    ->pluck('user_id');

                $prizes = $record->eventPrizes->map(function (EventPrize $prize) {
                    return collect(range(1, $prize->quantity))
                        ->map(fn () => new Fluent([
                            'event_prize_id' => $prize->id,
                            'user_id' => null,
                        ]));
                })->collapse();

                DB::beginTransaction();
                EventWinner::query()->toBase()->where('event_id', $record->id)->truncate();
                while ($prizes->isNotEmpty() && $users->isNotEmpty()) {
                    /** @var Fluent $prize */
                    $prize = $prizes->shift();
                    $prize->user_id = $users->random();

                    if ($repeat === YesOrNo::NO) {
                        $users = $users->reject($prize->user_id);
                    }

                    EventWinner::create([
                        'event_id' => $record->id,
                        'event_prize_id' => $prize->event_prize_id,
                        'user_id' => $prize->user_id,
                    ]);
                }
                DB::commit();
            });
    }
}
