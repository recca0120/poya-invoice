<?php

namespace Tests\Feature\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\RelationManagers\EventUserRelationManager;
use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\HasLoginUser;
use Tests\TestCase;

class EventUserRelationManagerTest extends TestCase
{
    use HasLoginUser;
    use LazilyRefreshDatabase;

    public function test_create_new_user(): void
    {
        $this->givenSuperAdminUser();

        /** @var Event $event */
        $event = Event::factory()->createOne();

        $testable = Livewire::test(EventUserRelationManager::class, [
            'ownerRecord' => $event, 'pageClass' => EditEvent::class,
        ])->assertOk();

        $data = [
            'user_id' => 1,
            'sn' => fake()->uuid(),
            'approved' => true,
        ];

        $testable
            ->mountTableAction('create')
            ->setTableActionData($data)
            ->assertTableActionDataSet($data)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('event_user', $data);
    }
}
