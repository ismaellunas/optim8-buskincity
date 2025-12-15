<?php

namespace App\Http\Responses;

use App\Services\LoginService;
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

            if (LoginService::isAdminLoginAttemptRoute($request->route())) {
                $user = $request->user();
                
                // Redirect City Administrators to Spaces
                if ($user->hasRole('city_administrator') && !$user->can('system.dashboard')) {
                    return redirect()->intended(route('admin.spaces.index'));
                }
                
                // Redirect other admin users to Dashboard
                if ($user->can('system.dashboard')) {
                    $home = 'admin_dashboard';
                }
            }

            return redirect()->intended(Fortify::redirects($home));
        }
    }
}
