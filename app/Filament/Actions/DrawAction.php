<?php

namespace App\Filament\Actions;

use App\Enums\YesNo;
use App\Models\Event;
use Filament\Actions\Action;
use Livewire\Component;

class DrawAction extends Action
{
    use HasDraw;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpHasDraw();

        $this->visible(fn (Event $record) => value(static function (Event $record) {
            return $record->ended && $record->eventPrizes()->exists();
        }, $record));

        $this->requiresConfirmation(static fn (Event $record) => value(static function (Event $record) {
            return $record->drawn === true;
        }, $record));

        $this->action(function (Component $livewire, array $data) {
            $result = $this->process(function (Event $record) use ($data) {
                return $this->processDraw($record, YesNo::from($data['repeat']) === YesNo::YES);
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
