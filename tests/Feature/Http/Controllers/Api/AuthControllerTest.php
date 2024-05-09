<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Feature\Filament\Resources\HasLoginUser;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use HasLoginUser;
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

        $response->assertJsonStructure([
            'data' => ['access_token', 'token_type'],
        ]);
    }
}
