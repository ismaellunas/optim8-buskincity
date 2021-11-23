<?php

namespace App\Providers;

use App\Actions\AuthenticateLoginAttempt;
use App\Actions\AuthenticateLoginView;
use App\Actions\Jetstream\DeleteUser;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);

        if (config('jetstream.stack') === 'inertia') {

            Fortify::loginView([new AuthenticateLoginView(), '__invoke']);

            Fortify::authenticateUsing([new AuthenticateLoginAttempt(), '__invoke']);

            $this->app->instance(LoginResponseContract::class, new LoginResponse());

            $this->app->instance(LogoutResponseContract::class, new LogoutResponse());
        }
    }

    /**
     * Configure the permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
