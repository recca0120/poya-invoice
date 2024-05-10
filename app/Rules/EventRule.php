<?php

namespace App\Rules;

use App\Enums\EventType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

readonly class EventRule implements ValidationRule
{
    public function __construct(private EventType $eventType)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = $this->eventType === EventType::INVOICE
            ? '/[A-Z]{2}\d{8}/'
            : '/POYA[A-Za-z0-9]{8}/';

        if (! Str::of($value)->test($pattern)) {
            $fail(__('validation.not_regex'));
        }
    }
}
