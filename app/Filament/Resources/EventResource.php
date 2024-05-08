<?php

namespace App\Filament\Resources;

use App\Enums\EventType;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->columns(['lg' => 3])
                    ->schema([
                        Forms\Components\Section::make()
                            ->columnSpan(['lg' => 2])
                            ->schema([
                                Forms\Components\TextInput::make('code')->label('活動代號')->required(),
                                Forms\Components\TextInput::make('name')->label('活動名稱')->required(),
                                Forms\Components\ToggleButtons::make('type')->label('登錄類型')
                                    ->options(EventType::class)
                                    ->default(EventType::INVOICE)
                                    ->grouped()
                                    ->inline()
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\RichEditor::make('terms')
                                    ->label('活動條款')
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('privacy')
                                    ->label('個人資料聲明')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Group::make()
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Forms\Components\Section::make()->schema([
                                    Forms\Components\DateTimePicker::make('started_at')
                                        ->label('活動開始時間')
                                        ->native(false)
                                        ->required(),
                                    Forms\Components\DateTimePicker::make('ended_at')
                                        ->label('活動結束時間')
                                        ->native(false)
                                        ->required(),
                                ]),
                                Forms\Components\Section::make()
                                    ->heading('Banner')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('banner')
                                            ->collection('banner')
                                            ->required(),
                                    ]),

                                Forms\Components\Section::make()
                                    ->heading('背景圖')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('background')
                                            ->collection('background')
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('活動代號'),
                Tables\Columns\TextColumn::make('name')->label('活動名稱'),
                Tables\Columns\TextColumn::make('started_at')->label('開始時間'),
                Tables\Columns\TextColumn::make('ended_at')->label('結束時間'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EventUserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
