<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property EventType $type
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

    public function eventUser(): HasMany
    {
        return $this->hasMany(EventUser::class);
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
}