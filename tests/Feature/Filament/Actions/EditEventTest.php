<?php

namespace Tests\Feature\Filament\Actions;

use const true;

use App\Enums\YesOrNo;
use App\Filament\Actions\DrawAction;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventUser;
use App\Models\EventWinner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasLoginUser;
use Tests\TestCase;

class EditEventTest extends TestCase
{
    use HasLoginUser;
    use LazilyRefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Event $event */
        $this->event = Event::factory()->createOne();
    }

    public function test_2_prizes_1_users(): void
    {
        $this->givenPrize(2);
        $this->givenUsers(1);

        $action = DrawAction::make('draw');
        $action->record($this->event);
        $action->call(['data' => ['repeat' => YesOrNo::NO->value]]);

        $this->assertDatabaseCount('event_winner', 1);
        $this->shouldBeRepeatWinners(false);
    }

    public function test_2_prizes_4_users(): void
    {
        $this->givenPrize(2);
        $this->givenUsers(4);

        $action = DrawAction::make('draw');
        $action->record($this->event);
        $action->call(['data' => ['repeat' => YesOrNo::NO->value]]);

        $this->assertDatabaseCount('event_winner', 2);
        $this->shouldBeRepeatWinners(false);
    }

    public function test_2_prizes_1_users_and_can_repeat_winner(): void
    {
        $this->givenPrize(2);
        $this->givenUsers(1);

        $action = DrawAction::make('draw');
        $action->record($this->event);
        $action->call(['data' => ['repeat' => YesOrNo::YES->value]]);

        $this->assertDatabaseCount('event_winner', 2);
        $this->shouldBeRepeatWinners(true);
    }

    public function test_2_prizes_4_users_and_can_repeat_winner(): void
    {
        $this->givenPrize(2);
        $this->givenUsers(4);

        $action = DrawAction::make('draw');
        $action->record($this->event);
        $action->call(['data' => ['repeat' => YesOrNo::YES->value]]);

        $this->assertDatabaseCount('event_winner', 2);
    }

    private function givenUsers(int $count, bool $approved = true): Collection
    {
        $eventUserFactory = EventUser::factory()->state(['event_id' => $this->event->id]);

        return $eventUserFactory->count($count)->create(['approved' => $approved]);
    }

    private function givenPrize($quantity): EventPrize
    {
        return EventPrize::factory()
            ->state(['event_id' => $this->event->id])
            ->createOne(['name' => fake()->name(), 'quantity' => $quantity]);
    }

    private function shouldBeRepeatWinners(bool $expected): void
    {
        $winners = EventWinner::all()->pluck('user_id');

        self::assertEquals($expected, count(array_filter(
            array_count_values($winners->toArray()),
            static fn ($value) => $value > 1
        )) > 0, $winners->toJson());
    }
}
