<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

class AuthController extends Controller
{
    #[OAT\Post(
        path: '/api/login',
    )]
    #[OAT\RequestBody(content: [
        new OAT\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OAT\Schema(
                required: ['email', 'password'],
                properties: [
                    new OAT\Property(property: 'email', type: 'string'),
                    new OAT\Property(property: 'password', type: 'string'),
                ]
            )
        ),
    ])]
    #[OAT\Response(
        response: 200,
        description: '登入',
        content: new OAT\MediaType(
            mediaType: 'application/json',
            schema: new OAT\Schema(properties: [
                new OAT\Property(property: 'access_token', type: 'string', example: 'xxxxxxxxx'),
                new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
            ])
        ),
    )]
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /** @var User $user */
        $user = User::where('email', $request->input('email'))->sole();
        $token = $user->createToken('default');

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }
}
