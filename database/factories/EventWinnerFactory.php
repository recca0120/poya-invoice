<?php

namespace Database\Factories;

use App\Models\EventPrize;
use App\Models\EventUser;
use App\Models\EventWinner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventWinnerFactory extends Factory
{
    protected $model = EventWinner::class;

    public function configure()
    {
        return $this->afterCreating(function (EventWinner $eventWinner) {
            $eventWinner->user_id = $eventWinner->eventUser->user_id;
            $eventWinner->saveQuietly();
        });
    }

    public function definition(): array
    {
        $userFactory = User::factory();

        return [
            'event_prize_id' => EventPrize::factory(),
            'event_user_id' => EventUser::factory()->for($userFactory),
            'user_id' => $userFactory,
        ];
    }
}
