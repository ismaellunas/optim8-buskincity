<?php

namespace App\Http\Responses;

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
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $home = 'login';

            if (Str::startsWith(
                str_replace(url('/'), '', url()->previous()),
                '/admin'
            )) {
                $home = 'admin_login';
            }

            session()->flash('message', __('The password is updated, please do login to continue with a new password.'));

            return Inertia::location(Fortify::redirects($home, '/'));
        }
    }
}
