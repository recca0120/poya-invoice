<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventUser;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class EventUserControllerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    private array $data = [
        'Status' => 'Success',
        'Message' => '',
        'Data' => [
            'CellPhone' => '0912345678',
            'Name' => '易小九',
            'OuterMemberCode' => '277123456789',
        ],
    ];

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
            ->dump()
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

    /**
     * @throws JsonException
     */
    private function givenPoyaUser(): void
    {
        $client = new Client();
        $client->addResponse(
            new Response(200, [], json_encode($this->data, JSON_THROW_ON_ERROR))
        );
        $this->swap(ClientInterface::class, $client);

        $this->withToken('2a094fa16dfb9bc48c23b18663d25b1f00cd375e');
    }
}
