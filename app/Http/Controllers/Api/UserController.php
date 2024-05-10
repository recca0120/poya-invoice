<?php

namespace App\Http\Controllers\Api;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\EventUser;
use Illuminate\Database\Eloquent\Builder;
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
        $eventType = $request->enum('type', EventType::class);
        $user = $request->user();

        $events = EventUser::query()
            ->with('event:id,name')
            ->when($eventType, function (Builder $query, $eventType) {
                return $query->whereRelation('event', 'type', $eventType);
            })
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(1000);

        return JsonResource::collection($events);
    }
}
