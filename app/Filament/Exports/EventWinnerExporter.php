<?php

namespace App\Filament\Exports;

use App\Models\EventUser;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EventWinnerExporter extends Exporter
{
    protected static ?string $model = EventUser::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')->label('獎項'),
            ExportColumn::make('winner.name')->label('會員姓名'),
            ExportColumn::make('winner.member_code')->label('會員卡號'),
            ExportColumn::make('winner.phone_number')->label('電話號碼'),
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
}
