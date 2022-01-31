<?php

namespace App\Http\Responses;

use App\Services\LoginService;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\FailedTwoFactorLoginResponse as FortifyFailedTFLoginResponse;

class FailedTwoFactorLoginResponse extends FortifyFailedTFLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        } else {
            $loginPage = 'login';
            $message = __('The provided two factor authentication code was invalid.');

            if (
                LoginService::isAdminTwoFactorLoginAttemptRoute($request->route())
            ) {
                $loginPage = 'admin_login';
            }

            return redirect()
                ->intended(Fortify::redirects($loginPage))
                ->withErrors(['email' => $message]);
        }
    }
}
