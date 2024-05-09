<?php

namespace App\Filament\Actions;

use App\Enums\YesOrNo;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Colors\Color;

class DrawAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('抽獎')
            ->icon('heroicon-o-gift')
            ->color(Color::Teal)
            ->visible(fn (Event $record) => $record->isEnd())
            ->form([
                ToggleButtons::make('repeat')
                    ->label('同會員可重複中獎')
                    ->options(YesOrNo::class)
                    ->grouped()
                    ->inlineLabel(false)
                    ->default(YesOrNo::NO->value),
            ])
            ->action(function (Event $record, array $data) {
                $prizes = $record->eventPrizes;
                $quantity = $prizes->sum('quantity');
                $users = $record
                    ->eventUsers()
                    ->where('approved', true)
                    ->pluck('user_id');
                dump($quantity);
                dump($users);
            });
    }
}
