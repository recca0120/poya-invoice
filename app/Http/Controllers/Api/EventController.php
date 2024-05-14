<?php

namespace App\Http\Controllers\Api;

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OAT;

class EventController extends Controller
{
    #[OAT\Get(
        path: '/api/event',
        parameters: [
            new OAT\QueryParameter(name: 'type', schema: new OAT\Schema(enum: EventType::class)),
        ]
    )]
    #[OAT\Response(
        response: 200,
        description: '取得活動資訊',
        content: new OAT\MediaType(
            mediaType: 'application/json',
            schema: new OAT\Schema(properties: [
                new OAT\Property(
                    property: 'data',
                    type: 'array',
                    items: new OAT\Items(ref: Event::class)
                ),
            ])
        ),
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $eventType = $request->enum('type', EventType::class);

        return JsonResource::collection(
            Event::query()
                ->where('started_at', '<=', now())
                ->where('ended_at', '>=', now())
                ->when($eventType, function (Builder $query, $eventType) {
                    return $query->where('type', $eventType);
                })
                ->orderByDesc('started_at')
                ->orderByDesc('name')
                ->get()
                ->append(['banner', 'background'])
                ->makeHidden(['deleted_at', 'media'])
        );
    }
}
