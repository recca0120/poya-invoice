<?php

namespace Tests\Feature\Filament\Actions;

use App\Filament\Actions\DrawAction;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasLoginUser;
use Tests\TestCase;
use const true;

class EditEventTest extends TestCase
{
    use LazilyRefreshDatabase;
    use HasLoginUser;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Event $event */
        $this->event = Event::factory()->createOne();

    }

    public function test_2_prizes_4_users_and_can_not_repeat_winner(): void
    {
        $this->givenPrize(2);
        $this->givenUsers(4, true);

        $action = DrawAction::make('draw');
        $action->record($this->event);
        $action->call(['data' => ['repeat' => false]]);

        $this->assertDatabaseCount('event_winners', 2);
    }

    private function givenUsers(int $count, bool $approved): Collection
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
}
