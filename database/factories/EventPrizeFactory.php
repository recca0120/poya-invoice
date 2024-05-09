<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventPrizeFactory extends Factory
{
    protected $model = EventPrize::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->name(),
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}
