<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property Event $event
 * @property EventPrize $eventPrize
 * @property User $user
 *
 * @mixin Builder
 */
class EventWinner extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'event_prize_id',
        'user_id',
    ];

    public function event(): HasOneThrough
    {
        return $this->hasOneThrough(Event::class, EventPrize::class);
    }

    public function eventPrize(): BelongsTo
    {
        return $this->belongsTo(EventPrize::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
