<?php

namespace App\Filament\Imports;

use App\Models\EventUser;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class EventUserImporter extends Importer
{
    protected static ?string $model = EventUser::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sn')
                ->label('發票號碼或活動序號')
                ->guess(['發票號碼或活動序號', 'sn'])
                ->requiredMapping()
                ->exampleHeader('發票號碼或活動序號')
                ->example('AB12345678')
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('name')
                ->label('姓名')
                ->guess(['姓名', 'name'])
                ->requiredMapping()
                ->exampleHeader('姓名')
                ->example('王小明')
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('member_card_number')
                ->label('會員卡號')
                ->guess(['會員卡號', 'member_card_number'])
                ->requiredMapping()
                ->exampleHeader('會員卡號')
                ->example('2770000000000')
                ->rules(['required', 'string'])
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('phone_number')
                ->label('手機號碼')
                ->guess(['手機號碼', 'phone_number'])
                ->requiredMapping()
                ->exampleHeader('手機號碼')
                ->example('0910000000')
                ->fillRecordUsing(fn () => null),
            ImportColumn::make('approved')
                ->label('是否符合活動條件')
                ->guess(['是否符合活動條件', 'approved'])
                ->requiredMapping()
                ->exampleHeader('是否符合活動條件')
                ->example('是')
                ->rules(['required'])
                ->fillRecordUsing(function (EventUser $record, string $state) {
                    return match ($state) {
                        '是' => true,
                        '否' => false,
                        default => filter_var($state, FILTER_VALIDATE_BOOLEAN)
                    };
                }),
        ];
    }

    public function resolveRecord(): ?EventUser
    {
        // return EventUser::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);
        $name = $this->data['name'];
        $memberCardNumber = $this->data['member_card_number'];
        $phoneNumber = $this->data['phone_number'];

        /** @var User $user */
        $user = User::createOrFirst(['member_card_number' => $memberCardNumber], [
            'member_card_number' => $memberCardNumber,
            'phone_number' => $phoneNumber,
            'name' => $name,
            'email' => $memberCardNumber.'@fake-poya.com.tw',
            'password' => Str::random(32),
        ]);

        return EventUser::firstOrNew([
            'event_id' => $this->options['event_id'],
            'user_id' => $user->id,
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
