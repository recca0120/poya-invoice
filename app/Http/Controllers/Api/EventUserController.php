<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Rules\EventRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class EventUserController extends Controller
{
    public function create(Request $request, $eventId): JsonResource
    {
        /** @var Event $event */
        $event = Event::query()
            ->where('started_at', '<=', now())
            ->where('ended_at', '>=', now())
            ->where('id', $eventId)
            ->sole();

        $validated = $request->validate([
            'code' => [
                'string',
                'required',
                new EventRule($event->type),
                Rule::unique('event_users', 'code')
                    ->where('event_id', $eventId),
            ],
        ]);

        return JsonResource::make($event->eventUsers()->create([
            'user_id' => $request->user()->id,
            'code' => $validated['code'],
        ]));
    }
}
