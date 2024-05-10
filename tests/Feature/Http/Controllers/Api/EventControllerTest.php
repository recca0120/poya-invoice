<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_list_available_events(): void
    {
        $this->givenLoginUser();

        Event::factory()->count(5)->create([
            'started_at' => now(),
            'ended_at' => now()->addWeek(),
        ]);
        Event::factory()->count(5)->create([
            'started_at' => now()->subWeek(),
            'ended_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/event')->assertOk();
        $response->assertJsonCount(5, 'data');
    }
}
