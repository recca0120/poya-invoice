<?php

namespace App\Filament\Imports;

use App\Models\EventUser;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class EventUserImporter extends Importer
{
    protected static ?string $model = EventUser::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user_id'),
            ImportColumn::make('sn'),
            ImportColumn::make('approved'),
        ];
    }

    public function resolveRecord(): ?EventUser
    {
        // return EventUser::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return (new EventUser())->fill([
            'event_id' => $this->options['event_id'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your event user import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
