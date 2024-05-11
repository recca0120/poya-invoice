<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property EventPrize $eventPrize
 * @property EventUser $eventUser
 * @property Event $event
 *
 * @mixin Builder
 */
class EventWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_prize_id',
        'event_user_id',
        'user_id',
    ];

    public function eventPrize(): BelongsTo
    {
        return $this->belongsTo(EventPrize::class);
    }

    public function eventUser(): BelongsTo
    {
        return $this->belongsTo(EventUser::class);
    }

    public function event(): HasOneThrough
    {
        return $this->hasOneThrough(Event::class, EventPrize::class);
    }
}
