<?php

namespace App\Providers;

use App\Models\User;
use App\Poya;
use GuzzleHttp\Client;
use Illuminate\Auth\RequestGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;

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

        $this->app->bind(ClientInterface::class, function () {
            return new Client();
        });

        $this->app->singleton(Poya::class, function () {
            return new Poya(app(ClientInterface::class), config('services.poya.base_url'));
        });

        Auth::extend('poya', static function () {
            return new RequestGuard(static function (Request $request) {
                $token = $request->bearerToken();
                $ttl = now()->addHours(24);

                return Cache::remember('poya-'.$token, $ttl, static function () use ($token) {
                    /** @var Poya $poya */
                    $poya = resolve(Poya::class);
                    $data = $poya->setToken($token)->user();

                    return User::firstOrCreate([
                        'member_code' => $data['outer_member_code'],
                        'phone_number' => $data['cell_phone'],
                    ], ['name' => $data['name']]);
                });
            }, resolve('request'), null);
        });
    }
}
