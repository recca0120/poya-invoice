<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function profile(Request $request): JsonResource
    {
        return JsonResource::make(
            $request->user()->makeHidden(['email_verified_at', 'phone_number'])
        );
    }

    public function event(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $events = EventUser::query()
            ->with('event:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(1000);

        return JsonResource::collection($events);
    }
}
