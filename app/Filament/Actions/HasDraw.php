<?php

namespace App\Filament\Actions;

use App\Enums\YesNo;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventUser;
use App\Models\EventWinner;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

trait HasDraw
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'draw';
    }

    protected function setUpHasDraw(): void
    {
        $this->label('抽獎');
        $this->icon('heroicon-o-gift');
        $this->color(Color::Teal);
        $this->successNotificationTitle('抽獎名單已出爐');

        $this->form([
            ToggleButtons::make('repeat')
                ->label('同會員可重複中獎')
                ->options(YesNo::class)
                ->grouped()
                ->inlineLabel(false)
                ->default(YesNo::NO->value),
        ]);
    }

    protected function processDraw(Event $record, bool $repeat): true
    {
        $subQuery = EventPrize::query()
            ->select('id')
            ->where('event_id', $record->id);
        EventWinner::query()->whereIn('event_prize_id', $subQuery)->delete();

        $prizes = $record->eventPrizes()
            ->where('quantity', '>', 0)
            ->get()
            ->map(function (EventPrize $prize) {
                return collect(range(1, $prize->quantity))->map(function () use ($prize) {
                    return new Fluent([
                        'event_prize_id' => $prize->id,
                        'event_user_id' => null,
                    ]);
                });
            });

        $prizes = $repeat
            ? $prizes
                ->map(fn (Collection $prizes) => $this->draw($record, $prizes))
                ->collapse()
            : $this->draw($record, $prizes->collapse()->shuffle());

        $prizes
            ->reject(fn ($prize) => ! $prize->event_user_id)
            ->each(fn ($prize) => EventWinner::create([
                'event_prize_id' => $prize->event_prize_id,
                'event_user_id' => $prize->event_user_id,
            ]));

        return true;
    }

    private function draw(Event $event, Collection $prizes): Collection
    {
        $randomUsers = $event
            ->eventUsers()
            ->where('approved', true)
            ->inRandomOrder()
            ->take($prizes->count())
            ->get();

        return $randomUsers
            ->map(function (EventUser $eventUser, int $index) use ($prizes) {
                $prize = $prizes->get($index);
                $prize->event_user_id = $eventUser->id;

                return $prize;
            });
    }
}
