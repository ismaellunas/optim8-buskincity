<?php

namespace App\Http\Responses;

use App\Services\LoginService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\PasswordUpdateResponse as FortifyPasswordUpdateResponse;
use Inertia\Inertia;

class PasswordUpdateResponse extends FortifyPasswordUpdateResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        } else {
            $home = 'login';

            if (LoginService::isAdminHomeUrl()) {
                $home = 'admin_login';
            }

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            session()->flash('message', __('The password is updated, please do login to continue with a new password.'));

            return Inertia::location(Fortify::redirects($home, '/'));
        }
    }
}
