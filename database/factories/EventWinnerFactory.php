<?php

namespace Database\Factories;

use App\Models\EventPrize;
use App\Models\EventUser;
use App\Models\EventWinner;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventWinnerFactory extends Factory
{
    protected $model = EventWinner::class;

    public function definition(): array
    {
        return [
            'event_prize_id' => EventPrize::factory(),
            'event_user_id' => EventUser::factory(),
        ];
    }
}
