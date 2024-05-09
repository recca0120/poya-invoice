<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property numeric $quantity
 * @property Event $event
 * @property Collection<int, EventWinner> $eventWinners
 *
 * @mixin Builder
 */
class EventPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eventWinners(): HasMany
    {
        return $this->hasMany(EventWinner::class);
    }
}
