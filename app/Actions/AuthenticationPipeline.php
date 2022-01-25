<?php

namespace App\Actions;

use App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Actions\{
    AttemptToAuthenticate,
    EnsureLoginIsNotThrottled,
    PrepareAuthenticatedSession,
};
use Illuminate\Http\Request;

class AuthenticationPipeline
{
    public function __invoke(Request $request)
    {
        return array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            RedirectIfTwoFactorAuthenticatable::class,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]);
    }
}