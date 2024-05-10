<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasUser;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use HasUser;
    use LazilyRefreshDatabase;

    public function test_login(): void
    {
        $user = $this->givenUser('user', [
            'password' => 'password',
        ]);

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $response->assertJsonStructure(['access_token', 'token_type']);
    }
}
