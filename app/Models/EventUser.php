<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $code
 * @property User $user
 *
 * @mixin Builder
 */
class EventUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'code',
        'approved',
    ];

    protected function casts(): array
    {
        return [
            'approved' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
