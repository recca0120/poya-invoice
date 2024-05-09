<?php

namespace App\Filament\Actions;

use App\Enums\YesNo;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventUser;
use App\Models\EventWinner;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Livewire\Component;

class DrawAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'draw';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('抽獎');
        $this->icon('heroicon-o-gift');
        $this->color(Color::Teal);
        $this->visible(fn (Event $record) => $record->ended);
        $this->successNotificationTitle('抽獎名單已出爐');
        $this->requiresConfirmation(static fn (Event $record) => $record->drawn === true);

        $this->form([
            ToggleButtons::make('repeat')
                ->label('同會員可重複中獎')
                ->options(YesNo::class)
                ->grouped()
                ->inlineLabel(false)
                ->default(YesNo::NO->value),
        ]);
        $this->action(function (Component $livewire, array $data) {
            $result = $this->process(function (Event $record) use ($data) {
                $repeat = YesNo::from($data['repeat']) === YesNo::YES;

                $subQuery = EventPrize::query()
                    ->select('id')
                    ->where('event_id', $record->id);
                EventWinner::query()->whereIn('event_prize_id', $subQuery)->delete();

                $prizes = $record->eventPrizes()
                    ->where('quantity', '>', 0)
                    ->get()
                    ->map(function (EventPrize $prize) {
                        return collect(range(1, $prize->quantity))->map(function () use ($prize) {
                            return new Fluent(['event_prize_id' => $prize->id, 'user_id' => null]);
                        });
                    });

                $prizes = $repeat
                    ? $prizes
                        ->map(fn (Collection $prizes) => $this->draw($record, $prizes))
                        ->collapse()
                    : $this->draw($record, $prizes->collapse()->shuffle());

                $prizes
                    ->reject(fn ($prize) => ! $prize->user_id)
                    ->each(fn ($prize) => EventWinner::create([
                        'event_prize_id' => $prize->event_prize_id,
                        'user_id' => $prize->user_id,
                    ]));

                return true;
            });

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
            $livewire->dispatch('refreshRelation');
        });
    }

    private function draw(Event $event, Collection $prizes): Collection
    {
        $availableUsers = $event
            ->eventUsers()
            ->where('approved', true)
            ->inRandomOrder()
            ->take($prizes->count())
            ->get();

        return $availableUsers
            ->map(function (EventUser $eventUser, int $index) use ($prizes) {
                $prize = $prizes->get($index);
                $prize->user_id = $eventUser->user_id;

                return $prize;
            });
    }
}
