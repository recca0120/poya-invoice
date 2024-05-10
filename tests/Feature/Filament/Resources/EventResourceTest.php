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
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_create_invoice_event(): void
    {
        /** @var User $user */
        $this->givenSuperAdmin();

        $testable = Livewire::test(CreateEvent::class)->assertOk();

        $code = (string) Str::ulid();
        $name = '活動1';
        $type = EventType::INVOICE;
        $privacy = fake()->paragraphs(5, true);
        $terms = fake()->paragraphs(5, true);
        $startedAt = now();
        $endedAt = now()->addDays(3);

        $testable
            ->fillForm([
                'code' => $code,
                'name' => $name,
                'type' => $type,
                'terms' => $terms,
                'privacy' => $privacy,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'banner' => UploadedFile::fake()->image('banner.jpg'),
                'background' => UploadedFile::fake()->image('background.jpg'),
            ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('events', [
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'terms' => $terms,
            'privacy' => $privacy,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
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
}
