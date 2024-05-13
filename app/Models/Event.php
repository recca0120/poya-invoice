<?php

namespace App\Models;

use App\Enums\EventType;
use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $name
 * @property EventType $type
 * @property Carbon $started_at
 * @property Carbon $ended_at
 * @property bool $ended
 * @property bool $drawn
 * @property Collection<int, EventPrize> $eventPrizes
 * @property Collection<int, EventUser> $eventUsers
 * @property Collection<int, EventWinner> $eventWinners
 *
 * @method static EventFactory factory($count = null, $state = [])
 *
 * @mixin Builder
 */
class Event extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'terms',
        'privacy',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function eventUsers(): HasMany
    {
        return $this->hasMany(EventUser::class);
    }

    public function eventPrizes(): HasMany
    {
        return $this->hasMany(EventPrize::class);
    }

    public function eventWinners(): HasManyThrough
    {
        return $this->hasManyThrough(EventWinner::class, EventPrize::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(EventUser::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('banner')->nonQueued();
        $this->addMediaConversion('background')->nonQueued();
    }

    protected function ended(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->ended_at->lessThanOrEqualTo(now());
        });
    }

    protected function drawn(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->eventWinners()->exists();
        });
    }

    protected function banner(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->getFirstMediaUrl('banner');
        });
    }

    protected function background(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->getFirstMediaUrl('background');
        });
    }
}
