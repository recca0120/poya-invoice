<?php

namespace Tests\Feature\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\RelationManagers\EventPrizesRelationManager;
use App\Models\Event;
use App\Models\EventWinner;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class EventPrizesRelationManagerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_create_prizes(): void
    {
        $this->givenSuperAdmin();

        /** @var Event $event */
        $event = Event::factory()->createOne();

        $testable = Livewire::test(EventPrizesRelationManager::class, [
            'ownerRecord' => $event, 'pageClass' => EditEvent::class,
        ])->assertOk();

        $data = [
            'name' => '100元即享券',
            'quantity' => 1,
        ];

        $testable
            ->mountTableAction('create')
            ->setTableActionData($data)
            ->assertTableActionDataSet($data)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('event_prizes', $data);
    }

    public function test_export_winners(): void
    {
        $this->givenSuperAdmin();

        /** @var Event $event */
        $event = Event::factory()->createOne();
        $eventWinners = EventWinner::factory()
            ->recycle($event)
            ->count(5)
            ->create();

        $testable = Livewire::test(EventPrizesRelationManager::class, [
            'ownerRecord' => $event, 'pageClass' => EditEvent::class,
        ])->assertOk();

        $storage = Storage::fake('public');

        $testable->mountTableAction('export');
        $testable->callMountedTableAction();

        $storage->assertExists('filament_exports/1/0000000000000001.csv');

        $data = Str::of($storage->get('filament_exports/1/0000000000000001.csv'))
            ->explode("\n")
            ->map(fn (string $line) => str_getcsv($line))
            ->first();

        $eventWinner = $eventWinners->first();
        $eventUser = $eventWinner->eventUser;
        $eventPrize = $eventWinner->eventPrize;
        $event = $eventUser->event;
        $user = $eventUser->user;

        $this->assertEquals([
            $eventPrize->name,
            $eventUser->code,
            $event->name,
            $user->name,
            $user->member_code,
            $user->phone_number,
        ], $data);
    }
}
