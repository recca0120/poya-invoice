<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Rules\EventRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OAT;

class EventUserController extends Controller
{
    #[OAT\Post(
        path: '/api/event/{id}',
        parameters: [
            new OAT\PathParameter(name: 'id', required: true),
        ]
    )]
    #[OAT\RequestBody(content: [
        new OAT\MediaType(
            mediaType: 'application/x-www-form-urlencoded',
            schema: new OAT\Schema(
                required: ['code'],
                properties: [
                    new OAT\Property(property: 'code', type: 'string', example: 'AB12345678'),
                ]
            )
        ),
    ])]
    #[OAT\Response(
        response: 201,
        description: '發票/序號 登錄',
        content: new OAT\MediaType(
            mediaType: 'application/json',
            schema: new OAT\Schema(properties: [
                new OAT\Property(property: 'data', properties: [
                    new OAT\Property(property: 'event_id', type: 'int', example: 1),
                    new OAT\Property(property: 'user_id', type: 'int', example: 1),
                    new OAT\Property(property: 'code', type: 'string', example: 'AB12345678'),
                ]),
            ])
        ),
    )]
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
