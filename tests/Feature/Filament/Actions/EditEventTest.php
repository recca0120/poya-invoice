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
    private EventPrize $firstPrize;
    private EventPrize $secondaryPrize;
    private EventPrize $thirdPrize;
    private EventPrize $otherPrize;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Event $event */
        $this->event = Event::factory()->createOne();

        $factory = EventPrize::factory()->state(['event_id' => $this->event->id]);
        $this->firstPrize = $factory->createOne(['name' => 'First Prize', 'quantity' => 1]);
        $this->secondaryPrize = $factory->createOne(['name' => 'Secondary Prize', 'quantity' => 2]);
        $this->thirdPrize = $factory->createOne(['name' => 'Third Prize', 'quantity' => 3]);
        $this->otherPrize = $factory->createOne(['name' => 'Third Prize', 'quantity' => 50]);
    }

    public function test_draw_action(): void
    {
        $this->givenUsers(100, true);
        $this->givenUsers(1, true);

        $action = DrawAction::make('draw');
        $action->record($this->event);
        $action->call(['data' => ['repeat' => false]]);
    }

    private function givenUsers(int $count, bool $approved): Collection
    {
        $eventUserFactory = EventUser::factory()->state(['event_id' => $this->event->id]);

        return $eventUserFactory->count($count)->create(['approved' => $approved]);
    }
}
