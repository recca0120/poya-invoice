<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
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
}
