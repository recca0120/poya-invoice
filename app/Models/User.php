<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $member_code
 * @property string $phone_number
 * @property string $email
 * @property Collection<int, Role> $roles
 *
 * @method static UserFactory factory($count = null, $state = [])
 *
 * @mixin Builder
 */
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasPanelShield, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'member_code',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function toWinnerString(): string
    {
        $lookup = [
            '姓名' => $this->name,
            '會員卡號' => $this->member_code,
            '電話號碼' => $this->phone_number,
        ];

        return implode('<br />', array_reduce(
            array_keys($lookup),
            static function (array $carry, string $key) use ($lookup) {
                $value = $lookup[$key];

                return $value ? [...$carry, $key.': '.$value] : $carry;
            },
            []
        ));
    }
}
