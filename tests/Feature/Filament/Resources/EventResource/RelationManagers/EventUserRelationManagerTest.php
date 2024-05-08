<?php

namespace Tests\Feature\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\RelationManagers\EventUserRelationManager;
use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
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

    public function test_import_event_user_from_csv(): void
    {
        $this->givenSuperAdminUser();

        /** @var Event $event */
        $event = Event::factory()->createOne();

        $testable = Livewire::test(EventUserRelationManager::class, [
            'ownerRecord' => $event, 'pageClass' => EditEvent::class,
        ])->assertOk();

        $headers = ['user_id', 'sn', 'approved'];
        $data = [
            ['1', '1', '1'],
        ];

        $csv = $this->createCsv($headers, $data);
        $testable
            ->mountTableAction('import')
            ->setTableActionData(['file' => $csv])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => 1,
            'sn' => 1,
            'approved' => 1,
        ]);
    }

    private function createCsv(array $headers, array $data): File
    {
        $content = implode("\n", array_map(
            static fn ($data) => implode(",", $data),
            [$headers, ...$data]
        ));

        return UploadedFile::fake()->createWithContent('test.csv', $content);
    }
}
