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
        $code = 'AB12345678';
        $user = $this->givenLoginUser();
        $event = $this->givenEvent();

        $this->postJson('/api/event/'.$event->id, ['code' => $code])
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'code' => $code,
                ],
            ]);

        $this->assertDatabaseHas('event_users', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'code' => $code,
        ]);
    }

    public function test_can_not_send_same_sn(): void
    {
        $code = 'AB12345678';

        $this->givenLoginUser();
        $event = $this->givenEvent();
        EventUser::factory()->for($event)->createOne(['code' => $code]);

        $this->postJson('/api/event/'.$event->id, ['code' => $code])
            ->assertJsonValidationErrors(['code' => 'The code has already been taken.']);
    }

    public function test_invalid_invoice(): void
    {
        $code = '12345678';

        $this->givenLoginUser();
        $event = $this->givenEvent();
        EventUser::factory()->for($event)->createOne(['code' => $code]);

        $this->postJson('/api/event/'.$event->id, ['code' => $code])
            ->assertJsonValidationErrors(['code' => 'The code field format is invalid.']);
    }

    public function test_invalid_sn(): void
    {
        $code = '12345678';

        $this->givenLoginUser();
        $event = $this->givenEvent(EventType::SN);
        EventUser::factory()->for($event)->createOne(['code' => $code]);

        $this->postJson('/api/event/'.$event->id, ['code' => $code])
            ->assertJsonValidationErrors(['code' => 'The code field format is invalid.']);
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
