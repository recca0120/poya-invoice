<?php

namespace Tests\Feature\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\RelationManagers\EventUsersRelationManager;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class EventUsersRelationManagerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_create_new_user(): void
    {
        $this->givenSuperAdmin();

        /** @var Event $event */
        $event = Event::factory()->createOne();

        $testable = Livewire::test(EventUsersRelationManager::class, [
            'ownerRecord' => $event, 'pageClass' => EditEvent::class,
        ])->assertOk();

        $data = [
            'user_id' => 1,
            'code' => fake()->uuid(),
            'approved' => true,
        ];

        $testable
            ->mountTableAction('create')
            ->setTableActionData($data)
            ->assertTableActionDataSet($data)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('event_users', $data);
    }

    public function test_import_exist_user_to_event_user_from_csv(): void
    {
        $this->givenSuperAdmin();

        /** @var Event $event */
        $event = Event::factory()->createOne();

        $testable = Livewire::test(EventUsersRelationManager::class, [
            'ownerRecord' => $event, 'pageClass' => EditEvent::class,
        ])->assertOk();

        $code = fake()->uuid();
        $name = '王小明';
        $memberCode = fake()->creditCardNumber();
        $phoneNumber = fake()->phoneNumber();

        /** @var User $user */
        $user = User::factory()->createOne([
            'member_code' => $memberCode,
            'phone_number' => $phoneNumber,
        ]);

        $headers = ['code', 'name', 'member_code', 'phone_number', 'approved'];
        $data = [
            [$code, $name, $memberCode, $phoneNumber, 1],
        ];

        $csv = $this->createCsv($headers, $data);
        $testable
            ->mountTableAction('import')
            ->setTableActionData(['file' => $csv])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('event_users', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'code' => $code,
            'approved' => 1,
        ]);
    }

    public function test_import_not_exist_user_to_event_user_from_csv(): void
    {
        $this->givenSuperAdmin();

        /** @var Event $event */
        $event = Event::factory()->createOne();

        $testable = Livewire::test(EventUsersRelationManager::class, [
            'ownerRecord' => $event, 'pageClass' => EditEvent::class,
        ])->assertOk();

        $code = fake()->uuid();
        $name = '王小明';
        $memberCode = fake()->creditCardNumber();
        $phoneNumber = fake()->phoneNumber();

        $headers = ['name', '發票號碼或活動序號', 'member_code', 'phone_number', 'approved'];
        $data = [
            [$name, $code, $memberCode, $phoneNumber, '是'],
        ];

        $csv = $this->createCsv($headers, $data);
        $testable
            ->mountTableAction('import')
            ->setTableActionData(['file' => $csv])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('event_users', [
            'event_id' => $event->id,
            'code' => $code,
            'approved' => 1,
        ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => $phoneNumber,
        ]);
    }

    private function createCsv(array $headers, array $data): File
    {
        $content = implode("\n", array_map(
            static fn ($data) => implode(',', $data),
            [$headers, ...$data]
        ));

        return UploadedFile::fake()->createWithContent('test.csv', $content);
    }
}
