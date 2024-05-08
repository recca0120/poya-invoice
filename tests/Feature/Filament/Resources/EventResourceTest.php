<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\EventType;
use App\Filament\Resources\EventResource\Pages\CreateEvent;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class EventResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_create_invoice_event(): void
    {
        /** @var User $user */
        $this->givenUser();

        $testable = Livewire::test(CreateEvent::class)->assertOk();

        $code = (string) Str::ulid();
        $name = '活動1';
        $type = EventType::INVOICE;
        $startAt = now();
        $endAt = now()->addDays(3);

        $testable
            ->fillForm([
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'banner' => UploadedFile::fake()->image('banner.jpg'),
                'background' => UploadedFile::fake()->image('background.jpg'),
            ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('events', [
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        /** @var Event $event */
        $event = Event::where('code', $code)->sole();

        $this->assertDatabaseHas('media', [
            'model_id' => $event->id,
            'model_type' => Event::class,
            'name' => 'banner',
        ]);

        $this->assertDatabaseHas('media', [
            'model_id' => $event->id,
            'model_type' => Event::class,
            'name' => 'background',
        ]);
    }

    public function test_create_sn_event(): void
    {
        $this->givenUser();

        $testable = Livewire::test(CreateEvent::class)->assertOk();

        $code = (string) Str::ulid();
        $name = '活動1';
        $type = EventType::SN;
        $startAt = now();
        $endAt = now()->addDays(3);

        $testable
            ->fillForm([
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'start_at' => $startAt,
                'end_at' => $endAt,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('events', [
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);
    }

    private function givenUser(): User
    {
        return tap(
            User::factory()->role('super_admin')->createOne(),
            static fn (User $user) => Livewire::actingAs($user)
        );
    }
}
