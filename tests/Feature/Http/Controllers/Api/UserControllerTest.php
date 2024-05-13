<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventUser;
use App\Models\EventWinner;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_get_profile(): void
    {
        $user = $this->givenLoginUser();

        $this->getJson('/api/user/profile')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'member_code' => $user->member_code,
                ],
            ]);
    }

    public function test_get_user_events(): void
    {
        $user = $this->givenLoginUser();
        /** @var Event $event */
        $event = Event::factory()->createOne();
        $eventUsers = EventUser::factory()->for($user)->for($event)->count(2)->create();
        $eventPrize = EventPrize::factory()->create(['event_id' => $event->id]);
        EventWinner::factory()->create([
            'event_user_id' => $eventUsers->first()->id, 'event_prize_id' => $eventPrize->id,
        ]);

        $this->getJson('/api/user/event')
            ->dump()
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'code',
                        'created_at',
                        'event' => ['name'],
                    ],
                ],
            ]);
    }

    public function test_get_user_invoice_events(): void
    {
        $user = $this->givenLoginUser();
        EventUser::factory()->for($user)->count(2)->create();

        $this->getJson('/api/user/event?type='.EventType::INVOICE->value)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'code',
                        'created_at',
                        'event' => ['name'],
                    ],
                ],
            ]);
    }

    public function test_get_user_sn_events(): void
    {
        $user = $this->givenLoginUser();
        EventUser::factory()->for($user)->count(2)->create();

        $this->getJson('/api/user/event?type='.EventType::SN->value)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
