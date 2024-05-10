<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class EventUserController extends Controller
{
    public function create(Request $request, $eventId)
    {
        $validated = $request->validate([
            'sn' => [
                'string',
                'required',
                Rule::unique('event_user', 'sn')
                    ->where('event_id', $eventId),
            ],
        ]);

        /** @var Event $event */
        $event = Event::query()
            ->where('started_at', '<=', now())
            ->where('ended_at', '>=', now())
            ->where('id', $eventId)
            ->sole();

        return JsonResource::make($event->eventUsers()->create([
            'user_id' => $request->user()->id,
            'sn' => $validated['sn'],
        ]));
    }
}
