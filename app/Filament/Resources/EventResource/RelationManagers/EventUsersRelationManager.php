<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\YesNo;
use App\Filament\Imports\EventUserImporter;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Event getOwnerRecord()
 */
class EventUsersRelationManager extends RelationManager
{
    protected static ?string $title = '抽獎名單';

    protected static ?string $pluralLabel = '抽獎名單';

    protected static ?string $modelLabel = '抽獎名單';

    protected static ?string $pluralModelLabel = '抽獎名單';

    protected static string $relationship = 'eventUsers';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var Event $ownerRecord */
        return $ownerRecord->eventUsers->where('approved', true)->count();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('會員')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label(value(static fn (Event $event) => $event->type->getColumnName(), $this->getOwnerRecord()))
                    ->required(),
                Forms\Components\ToggleButtons::make('approved')
                    ->label('符合條件')
                    ->options(YesNo::class)
                    ->grouped()
                    ->default(YesNo::YES->value),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['event', 'user']))
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('event.code')->label('活動代號'),
                Tables\Columns\TextColumn::make('user.name')->label('姓名'),
                Tables\Columns\TextColumn::make('user.member_code')->label('會員卡號'),
                Tables\Columns\TextColumn::make('user.phone_number')->label('手機號碼'),
                Tables\Columns\TextColumn::make('code')
                    ->label(value(static fn (Event $event) => $event->type->getColumnName(), $this->getOwnerRecord())),
                Tables\Columns\ToggleColumn::make('approved')->label('符合條件'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\ImportAction::make()
                    ->importer(EventUserImporter::class)
                    ->options([
                        'event_id' => $this->getOwnerRecord()->id,
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
