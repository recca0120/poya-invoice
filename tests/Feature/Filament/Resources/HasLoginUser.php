<?php

namespace Tests\Feature\Filament\Resources;

use App\Models\User;
use Livewire\Livewire;

trait HasLoginUser
{
    protected function givenSuperAdmin(): User
    {
        return $this->givenUser('super_admin');
    }

    private function givenUser($role): User
    {
        return tap(
            User::factory()->role($role)->createOne(),
            static fn (User $user) => Livewire::actingAs($user)
        );
    }
}
