<?php

namespace App\Http\Responses;

use App\Services\LoginService;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse as FortifyTFLoginResponse;

class TwoFactorLoginResponse extends FortifyTFLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        } else {
            $home = 'dashboard';

            if (LoginService::isAdminTwoFactorLoginAttemptRoute($request->route())) {
                $user = $request->user();

                if (
                    $user->hasRole(config('permission.role_names.city_admin'))
                    && ! $user->can('system.dashboard')
                ) {
                    return redirect()->intended(route('admin.spaces.index'));
                }

                if ($user->can('system.dashboard')) {
                    $home = 'admin_dashboard';
                }
            }

            return redirect()->intended(Fortify::redirects($home));
        }
    }
}
