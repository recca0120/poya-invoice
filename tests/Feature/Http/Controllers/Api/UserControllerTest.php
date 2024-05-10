<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\EventUser;
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
        EventUser::factory()->for($user)->count(2)->create();

        $this->getJson('/api/user/event')
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
