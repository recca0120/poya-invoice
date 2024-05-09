<?php

namespace App\Filament\Tables\Actions;

use App\Enums\YesNo;
use App\Filament\Actions\HasDraw;
use App\Models\Event;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;

class DrawAction extends Action
{
    use HasDraw;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpHasDraw();

        $this->visible(fn (RelationManager $livewire) => value(static function (Event $record) {
            return $record->ended && $record->eventPrizes()->exists();
        }, $livewire->getOwnerRecord()));

        $this->requiresConfirmation(static fn (RelationManager $livewire) => value(static function (Event $record) {
            return $record->drawn === true;
        }, $livewire->getOwnerRecord()));

        $this->action(function (RelationManager $livewire, array $data) {
            $result = $this->process(function () use ($livewire, $data) {
                return $this->processDraw($livewire->getOwnerRecord(), YesNo::from($data['repeat']) === YesNo::YES);
            });

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
            $livewire->dispatch('refreshRelation');
        });
    }
}
