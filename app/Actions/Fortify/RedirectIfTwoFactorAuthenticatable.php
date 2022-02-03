<?php

namespace App\Actions\Fortify;

use App\Helpers\Url;
use App\Services\LoginService;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as FortifyRedirect;

class RedirectIfTwoFactorAuthenticatable extends FortifyRedirect
{
    // Override from parent class on fortify
    protected function twoFactorChallengeResponse($request, $user)
    {
        if ($request->wantsJson()) {
            return parent::twoFactorChallengeResponse($request, $user);
        } else {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->filled('remember'),
                'login.recovery.id' => $user->getKey(),
                'login.recovery.remember' => $request->filled('remember'),
            ]);

            return $this->setRedirect();
        }
    }

    private function setRedirect()
    {
        $url = url()->previous();
        $route = Url::getRoute($url);

        return (LoginService::isAdminLoginRoute($route))
            ? redirect()->route('admin.two-factor.login')
            : redirect()->route('two-factor.login');
    }
}