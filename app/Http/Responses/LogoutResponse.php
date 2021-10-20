<?php

namespace App\Http\Responses;

use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LogoutResponse as FortifyLogoutResponse;

class LogoutResponse extends FortifyLogoutResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        } else {
            $home = 'login';

            if (Str::startsWith(
                str_replace(url('/'), '', url()->previous()),
                '/admin'
            )) {
                $home = 'admin_login';
            }

            return redirect(Fortify::redirects($home, '/'));
        }
    }
}
