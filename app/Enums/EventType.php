<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EventType: string implements HasLabel
{
    case INVOICE = 'invoice';
    case SN = 'sn';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INVOICE => '發票登錄',
            self::SN => '序號登錄'
        };
    }

    public function getColumnName(): string
    {
        return match ($this) {
            self::INVOICE => '發票號碼',
            self::SN => '活動序號'
        };
    }
}
