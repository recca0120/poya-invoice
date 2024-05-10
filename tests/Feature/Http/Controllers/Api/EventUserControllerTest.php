<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class EventUserControllerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_create_invoice(): void
    {
        $sn = 'AB12345678';
        $user = $this->givenLoginUser();
        $event = $this->givenEvent();

        $this->postJson('/api/event/'.$event->id, ['sn' => $sn])
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'sn' => $sn,
                ],
            ]);

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'sn' => $sn,
        ]);
    }

    public function test_can_not_send_same_sn(): void
    {
        $sn = 'AB12345678';

        $this->givenLoginUser();
        $event = $this->givenEvent();
        EventUser::factory()->for($event)->createOne(['sn' => $sn]);

        $this->postJson('/api/event/'.$event->id, ['sn' => $sn])
            ->assertJsonValidationErrors(['sn' => 'The sn has already been taken.']);
    }

    public function test_invalid_invoice(): void
    {
        $sn = '12345678';

        $this->givenLoginUser();
        $event = $this->givenEvent();
        EventUser::factory()->for($event)->createOne(['sn' => $sn]);

        $this->postJson('/api/event/'.$event->id, ['sn' => $sn])
            ->assertJsonValidationErrors(['sn' => 'The sn field format is invalid.']);
    }

    public function test_invalid_sn(): void
    {
        $sn = '12345678';

        $this->givenLoginUser();
        $event = $this->givenEvent(EventType::SN);
        EventUser::factory()->for($event)->createOne(['sn' => $sn]);

        $this->postJson('/api/event/'.$event->id, ['sn' => $sn])
            ->assertJsonValidationErrors(['sn' => 'The sn field format is invalid.']);
    }

    private function givenEvent($type = EventType::INVOICE): Event
    {
        return Event::factory()->createOne([
            'type' => $type,
            'started_at' => now(),
            'ended_at' => now()->addWeek(),
        ]);
    }
}
