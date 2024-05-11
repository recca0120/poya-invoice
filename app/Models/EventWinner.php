<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property EventPrize $eventPrize
 * @property EventUser $eventUser
 *
 * @mixin Builder
 */
class EventWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_user_id',
        'event_prize_id',
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
}
