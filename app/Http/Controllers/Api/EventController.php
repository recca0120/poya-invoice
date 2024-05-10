<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class EventController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return JsonResource::collection(
            Event::query()
                ->where('started_at', '<=', now())
                ->where('ended_at', '>=', now())
                ->orderByDesc('started_at')
                ->orderByDesc('name')
                ->get()
        );
    }
}
