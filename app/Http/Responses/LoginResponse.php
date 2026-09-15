<?php

namespace App\Http\Responses;

use App\Services\LoginService;
use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponse;

class LoginResponse extends FortifyLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        }

        return redirect()->intended(LoginService::redirectPathFor($request->user()));
    }
}
