<?php

namespace Tests\Feature\Filament\Resources;

use App\Models\User;
use Livewire\Livewire;

trait HasUser
{
    protected function givenSuperAdmin(): User
    {
        return $this->givenLoginUser('super_admin');
    }

    private function givenLoginUser(?string $role = ''): User
    {
        return tap(
            $this->givenUser($role),
            static fn (User $user) => Livewire::actingAs($user)
        );
    }

    private function givenUser(?string $role = '', array $attributes = []): User
    {
        return User::factory()->role($role)->createOne($attributes);
    }
}
