<?php

namespace App\Http\Responses;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponse;

class LoginResponse extends FortifyLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        } else {
            $home = 'dashboard';

            if (
                $request->route()->getName() == 'admin.login.attempt'
                && $request->user()->can('system.dashboard')
            ) {
                $home = 'admin_dashboard';
            }

            return redirect()->intended(Fortify::redirects($home));
        }
    }
}
