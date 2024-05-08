<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EventUser extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'sn',
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
