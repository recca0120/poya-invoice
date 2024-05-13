<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    private static string $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'code' => fake()->uuid(),
            'name' => fake()->name(),
            'type' => EventType::INVOICE,
            'terms' => fake()->paragraphs(5, true),
            'privacy' => fake()->paragraphs(5, true),
            'started_at' => now(),
            'ended_at' => now()->addWeek(),
        ];
    }

    public function banner(): EventFactory
    {
        return $this->afterCreating(function (Event $event) {
            $event->addMediaFromBase64(static::$png)->usingFileName('banner.png')->toMediaLibrary('banner');
        });
    }

    public function background(): EventFactory
    {
        return $this->afterCreating(function (Event $event) {
            $event->addMediaFromBase64(static::$png)->usingFileName('background.png')->toMediaLibrary('banner');
        });
    }
}
