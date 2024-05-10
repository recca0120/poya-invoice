<?php

namespace Database\Factories;

use App\Models\EventPrize;
use App\Models\EventWinner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventWinnerFactory extends Factory
{
    protected $model = EventWinner::class;

    public function definition(): array
    {
        return [
            'event_prize_id' => EventPrize::factory(),
            'user_id' => User::factory(),
        ];
    }
}
