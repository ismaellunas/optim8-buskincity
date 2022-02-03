<?php

namespace App\Providers;

use App\Actions\{
    AuthenticateLoginAttempt,
    AuthenticateLoginView,
    AuthenticationPipeline,
    Fortify\PasswordResetLinkView,
    Jetstream\DeleteUser,
    TwoFactorChallengeView
};
use App\Http\Responses\{
    LoginResponse,
    LogoutResponse,
    TwoFactorLoginResponse,
    FailedTwoFactorLoginResponse,
};
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\{
    LoginResponse as LoginResponseContract,
    LogoutResponse as LogoutResponseContract,
    TwoFactorLoginResponse as TwoFactorLoginResponseContract,
    FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract
};
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

            Fortify::twoFactorChallengeView([new TwoFactorChallengeView(), '__invoke']);

            Fortify::requestPasswordResetLinkView([new PasswordResetLinkView(), '__invoke']);

            Fortify::loginView([new AuthenticateLoginView(), '__invoke']);

            Fortify::authenticateUsing([new AuthenticateLoginAttempt(), '__invoke']);

            $this->app->instance(LoginResponseContract::class, new LoginResponse());

            $this->app->instance(LogoutResponseContract::class, new LogoutResponse());

            $this->app->instance(TwoFactorLoginResponseContract::class, new TwoFactorLoginResponse());

            $this->app->instance(FailedTwoFactorLoginResponseContract::class, new FailedTwoFactorLoginResponse());

            Fortify::authenticateThrough([new AuthenticationPipeline(), '__invoke']);
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
