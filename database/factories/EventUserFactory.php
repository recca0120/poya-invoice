<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventUserFactory extends Factory
{
    protected $model = EventUser::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'code' => fake()->uuid(),
            'approved' => fake()->boolean(),
        ];
    }
}
