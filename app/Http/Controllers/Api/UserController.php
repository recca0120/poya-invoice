<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function profile(Request $request): JsonResource
    {
        return JsonResource::make(
            $request->user()
                ->makeHidden([
                    'email_verified_at',
                    'phone_number',
                ])
        );
    }
}
