<?php

namespace App\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\EventUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

class UserController extends Controller
{
    #[OAT\Get(
        path: '/api/user/profile',
        parameters: [
            new OAT\QueryParameter(name: 'type', schema: new OAT\Schema(enum: EventType::class)),
        ]
    )]
    #[OAT\Response(
        response: 200,
        description: '參與活動',
        content: new OAT\MediaType(
            mediaType: 'application/json',
            schema: new OAT\Schema(properties: [
                new OAT\Property(property: 'data', properties: [
                    new OAT\Property(property: 'id', type: 'int', example: 1),
                    new OAT\Property(property: 'name', type: 'string', example: '王小明'),
                    new OAT\Property(property: 'email', type: 'string', example: 'recca0120@gmail.com'),
                    new OAT\Property(property: 'member_code', type: 'string', example: '2770000000000'),
                    new OAT\Property(property: 'phone_number', type: 'string', example: '0912345678'),
                    new OAT\Property(property: 'created_at', type: 'datetime', example: '2024-05-13T18:41:29.000000Z'),
                    new OAT\Property(property: 'updated_at', type: 'datetime', example: '2024-05-13T18:41:29.000000Z'),
                ]),
            ])
        ),
    )]
    public function profile(Request $request): JsonResource
    {
        return JsonResource::make(
            $request->user()->makeHidden(['email_verified_at'])
        );
    }

    #[OAT\Get(
        path: '/api/user/event',
        parameters: [
            new OAT\QueryParameter(name: 'type', schema: new OAT\Schema(enum: EventType::class)),
        ]
    )]
    #[OAT\Response(
        response: 200,
        description: '參與活動',
        content: new OAT\MediaType(
            mediaType: 'application/json',
            schema: new OAT\Schema(properties: [
                new OAT\Property(property: 'data', properties: [
                    new OAT\Property(property: 'id', type: 'int', example: 1),
                    new OAT\Property(property: 'event_id', type: 'int', example: 1),
                    new OAT\Property(property: 'user_id', type: 'int', example: 1),
                    new OAT\Property(property: 'code', type: 'string', example: 'AB12345678'),
                    new OAT\Property(property: 'approved', type: 'bool', example: true),
                    new OAT\Property(property: 'created_at', type: 'datetime', example: '2024-05-13T18:41:29.000000Z'),
                    new OAT\Property(property: 'updated_at', type: 'datetime', example: '2024-05-13T18:41:29.000000Z'),
                    new OAT\Property(property: 'event', properties: [
                        new OAT\Property(property: 'id', type: 'int', example: 1),
                        new OAT\Property(property: 'name', type: 'string', example: '活動名稱'),
                    ]),
                    new OAT\Property(property: 'event_prizes', type: 'array', items: new OAT\Items(properties: [
                        new OAT\Property(property: 'id', type: 'int', example: 1),
                        new OAT\Property(property: 'name', type: 'string', example: '按摩椅'),
                    ])),
                ]),
            ])
        ),
    )]
    public function event(Request $request): AnonymousResourceCollection
    {
        $eventType = $request->enum('type', EventType::class);
        $user = $request->user();

        $events = EventUser::query()
            ->with('event:id,name')
            ->with('eventPrizes:event_prizes.id,event_prizes.name')
            ->when($eventType, function (Builder $query, $eventType) {
                return $query->whereRelation('event', 'type', $eventType);
            })
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(1000);

        return JsonResource::collection($events);
    }
}
