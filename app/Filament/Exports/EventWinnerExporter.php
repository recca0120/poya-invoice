<?php

namespace App\Filament\Exports;

use App\Models\EventUser;
use App\Models\EventWinner;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class EventWinnerExporter extends Exporter
{
    protected static ?string $model = EventUser::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('event_name')
                ->label('活動名稱')
                ->getStateUsing(fn (EventWinner $record) => $record->eventUser->event->name),
            ExportColumn::make('code')
                ->label('序號')
                ->getStateUsing(fn (EventWinner $record) => $record->eventUser->code),
            ExportColumn::make('prize_name')
                ->label('獎項')
                ->getStateUsing(fn (EventWinner $record) => $record->eventPrize->name),
            ExportColumn::make('event_name')
                ->label('活動名稱')
                ->getStateUsing(fn (EventWinner $record) => $record->eventUser->event->name),
            ExportColumn::make('user_name')
                ->label('會員姓名')
                ->getStateUsing(fn (EventWinner $record) => $record->eventUser->user->name),
            ExportColumn::make('user_member_code')
                ->label('會員卡號')
                ->getStateUsing(fn (EventWinner $record) => $record->eventUser->user->member_code),
            ExportColumn::make('user_phone_number')
                ->label('電話號碼')
                ->getStateUsing(fn (EventWinner $record) => $record->eventUser->user->phone_number),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your event user export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return EventWinner::query()
            ->with('eventUser')
            ->with('eventUser.user')
            ->with('eventUser.event:id,name')
            ->with('eventPrize')
            ->orderBy('event_prize_id')
            ->orderBy('created_at');
    }
}
