<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Rawilk\FilamentPasswordInput\Password;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $label = '用戶';

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('姓名'),
                Forms\Components\TextInput::make('email')->email(),
                Password::make('password')
                    ->label('密碼')
                    ->copyable()
                    ->regeneratePassword()
                    ->required(static fn (string $operation) => $operation === 'create')
                    ->rules(config('filament-breezy.password_rules', 'max:8'))
                    ->beforeStateDehydrated(function (Password $component, $state) {
                        if ($state) {
                            $component->state(Hash::make($state));
                        }
                    }),
                Forms\Components\Select::make('roles')->label('角色')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('roles'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('姓名'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('roles')->label('角色')
                    ->getStateUsing(function (User $record) {
                        return $record->roles->pluck('name', 'id');
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('member_code')->label('會員卡號'),
                Tables\Columns\TextColumn::make('phone_number')->label('電話'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Impersonate::make()->redirectTo(Filament::getUrl()),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
