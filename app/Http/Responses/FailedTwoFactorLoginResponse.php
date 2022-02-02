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
            $this->setSessionUser($request);

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

    private function setSessionUser(Request $request): void
    {
        $userId = $request->session()->get('login.recovery.id');
        $remember = $request->session()->get('login.recovery.remember');

        $request->session()->put([
            'login.id' => $userId,
            'login.remember' => $remember,
        ]);
    }
}
