<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_list_available_invoice_events(): void
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

        $response = $this->getJson('/api/event?type='.EventType::INVOICE->value)->assertOk();
        $response->assertJsonCount(5, 'data');
    }

    public function test_list_available_sn_events(): void
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

        $response = $this->getJson('/api/event?type='.EventType::SN->value)->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}
