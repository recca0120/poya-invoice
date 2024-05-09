<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum YesNo: string implements HasColor, HasLabel
{
    case YES = 'yes';
    case NO = 'no';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::YES => '是',
            self::NO => '否',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::YES => 'primary',
            self::NO => 'danger',
        };
    }
}
