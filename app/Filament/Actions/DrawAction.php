<?php

namespace App\Filament\Actions;

use App\Enums\YesNo;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventWinner;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Collection;
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
                    ->options(YesNo::class)
                    ->grouped()
                    ->inlineLabel(false)
                    ->default(YesNo::NO->value),
            ])
            ->requiresConfirmation(static fn (Event $record) => $record->drawn === true)
            ->action(function (array $data) {
                $this->process(function (Event $record) use ($data) {
                    $repeat = YesNo::from($data['repeat']) === YesNo::NO;

                    $availableUsers = $record
                        ->eventUsers()
                        ->where('approved', true)
                        ->pluck('user_id');

                    /** @var Collection<int, object{event_prize_id: int, 'user_id': ?int}> $availablePrizes */
                    $availablePrizes = $record->eventPrizes
                        ->map(fn (EventPrize $prize) => collect(range(1, $prize->quantity))
                            ->map(fn () => new Fluent(['event_prize_id' => $prize->id, 'user_id' => null])))
                        ->collapse();

                    EventWinner::query()->toBase()->where('event_id', $record->id)->truncate();
                    while (true) {
                        if ($availableUsers->isEmpty()) {
                            break;
                        }

                        $drawablePrizes = $availablePrizes->reject(fn ($prize) => $prize->user_id);

                        if ($drawablePrizes->isEmpty()) {
                            break;
                        }

                        /** @var object{event_prize_id: int, user_id: ?int} $currentPrize */
                        $currentPrize = $drawablePrizes->first();

                        $drawableUsers = $availableUsers->diff(
                            $availablePrizes->filter(function ($prize) use ($currentPrize) {
                                return $prize->user_id && $prize->event_prize_id === $currentPrize->event_prize_id;
                            })->pluck('user_id')
                        );

                        if ($drawableUsers->isEmpty()) {
                            $availablePrizes = $availablePrizes->reject(function ($prize) use ($currentPrize) {
                                return $prize->event_prize_id === $currentPrize->event_prize_id && ! $prize->user_id;
                            });

                            continue;
                        }

                        $currentPrize->user_id = $drawableUsers->random();

                        if ($repeat) {
                            $availableUsers = $availableUsers->reject($currentPrize->user_id);
                        }
                    }

                    DB::beginTransaction();
                    $availablePrizes
                        ->reject(fn ($prize) => ! $prize->user_id)
                        ->unique(fn ($prize) => [$prize->event_prize_id, $prize->user_id])
                        ->each(function ($prize) {
                            return EventWinner::create([
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
