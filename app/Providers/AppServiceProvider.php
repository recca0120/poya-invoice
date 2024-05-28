<?php

namespace App\Providers;

use App\Models\User;
use App\Poya;
use Illuminate\Auth\RequestGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::before(static fn (User $user) => $user->hasRole('super_admin'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        Auth::extend('poya', static function () {
            return new RequestGuard(static function (Request $request) {
                /** @var Poya $poya */
                $poya = resolve(Poya::class);
                $data = $poya->setToken($request->bearerToken())->user();

                return User::firstOrCreate([
                    'member_code' => $data['outer_member_code'],
                    'phone_number' => $data['cell_phone'],
                ], ['name' => $data['name']]);
            }, resolve('request'), null);
        });
    }
}
