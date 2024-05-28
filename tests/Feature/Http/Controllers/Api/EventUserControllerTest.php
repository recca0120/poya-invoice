<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use JsonException;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class EventUserControllerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        $this->assertDatabaseHas('users', [
            'phone_number' => $this->data['Data']['CellPhone'],
            'name' => $this->data['Data']['Name'],
            'member_code' => $this->data['Data']['OuterMemberCode'],
        ]);

        parent::tearDown();
    }

    /**
     * @throws JsonException
     */
    public function test_create_invoice(): void
    {
        $this->givenPoyaUser();

        $code = 'AB12345678';
        $event = $this->givenEvent();

        $this
            ->postJson('/api/event/'.$event->id, ['code' => $code])
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'event_id' => $event->id,
                    'code' => $code,
                ],
            ]);

        $this->assertDatabaseHas('event_users', [
            'event_id' => $event->id,
            'code' => $code,
        ]);
    }

    /**
     * @throws JsonException
     */
    public function test_can_not_send_same_sn(): void
    {
        $this->givenPoyaUser();
        $code = 'AB12345678';

        $event = $this->givenEvent();
        EventUser::factory()->for($event)->createOne(['code' => $code]);

        $this->postJson('/api/event/'.$event->id, ['code' => $code])
            ->assertJsonValidationErrors(['code' => 'The code has already been taken.']);
    }

    /**
     * @throws JsonException
     */
    public function test_invalid_invoice(): void
    {
        $this->givenPoyaUser();
        $code = '12345678';

        $event = $this->givenEvent();
        EventUser::factory()->for($event)->createOne(['code' => $code]);

        $this->postJson('/api/event/'.$event->id, ['code' => $code])
            ->assertJsonValidationErrors(['code' => 'The code field format is invalid.']);
    }

    /**
     * @throws JsonException
     */
    public function test_invalid_sn(): void
    {
        $this->givenPoyaUser();
        $code = '12345678';

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
