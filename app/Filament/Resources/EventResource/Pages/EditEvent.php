<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Enums\YesOrNo;
use App\Filament\Resources\EventResource;
use App\Models\Event;
use Filament\Actions;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('draw')
                ->label('抽獎')
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
                ]),
            // Actions\DeleteAction::make(),
            // Actions\ForceDeleteAction::make(),
            // Actions\RestoreAction::make(),
        ];
    }
}
