<?php

namespace App\Http\Responses;

use App\Services\LoginService;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Responses\FailedTwoFactorLoginResponse as FortifyFailedTFLoginResponse;

class FailedTwoFactorLoginResponse extends FortifyFailedTFLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        } else {
            $routeName = 'two-factor.login';
            $message = __('The provided two factor authentication code was invalid.');

            if (
                LoginService::isAdminTwoFactorLoginAttemptRoute($request->route())
            ) {
                $routeName = 'admin.two-factor.login';
            }

            return redirect()
                ->route($routeName)
                ->withErrors(['email' => $message]);
        }
    }
}
