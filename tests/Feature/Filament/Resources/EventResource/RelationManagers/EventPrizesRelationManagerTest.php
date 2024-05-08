<?php

namespace Tests\Feature\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\RelationManagers\EventPrizesRelationManager;
use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\HasLoginUser;
use Tests\TestCase;

class EventPrizesRelationManagerTest extends TestCase
{
    use HasLoginUser;
    use LazilyRefreshDatabase;

    public function test_create_prizes(): void
    {
        $this->givenSuperAdminUser();

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
}