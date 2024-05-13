<?php

namespace App\Http\Controllers\Api;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class EventController extends Controller
{
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
        );
    }
}
