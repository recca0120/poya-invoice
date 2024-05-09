<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthController extends Controller
{
    public function login(Request $request): JsonResource
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /** @var User $user */
        $user = User::where('email', $request->input('email'))->sole();
        $token = $user->createToken('default');

        return JsonResource::make([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }
}
