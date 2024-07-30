<?php

namespace Tests\Feature\Filament\Resources;

use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Illuminate\Support\Fluent;
use JsonException;
use Livewire\Livewire;
use Psr\Http\Client\ClientInterface;

trait HasUser
{
    protected array $data = [
        'Status' => 'Success',
        'Message' => '',
        'Data' => [
            'CellPhone' => '0912345678',
            'Name' => '易小九',
            'OuterMemberCode' => '277123456789',
        ],
    ];

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

    /**
     * @throws JsonException
     */
    private function givenPoyaUser(): Fluent
    {
        $client = new Client;
        $client->addResponse(
            new Response(200, [], json_encode($this->data, JSON_THROW_ON_ERROR))
        );
        $this->swap(ClientInterface::class, $client);

        $this->withToken('2a094fa16dfb9bc48c23b18663d25b1f00cd375e');

        return new Fluent([
            'member_code' => $this->data['Data']['OuterMemberCode'],
        ]);
    }

    private function givenCreatedPoyaUser(): User
    {
        return tap(
            $this->givenUser('', [
                'member_code' => $this->data['Data']['OuterMemberCode'],
            ]),
            fn (User $user) => $this->actingAs($user, 'poya')
        );
    }
}
