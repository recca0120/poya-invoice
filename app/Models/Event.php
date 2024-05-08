<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 *
 * @mixin Builder
 */
class Event extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

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

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('banner')->nonQueued();
        $this->addMediaConversion('background')->nonQueued();
    }
}
