<?php

namespace App\Http\Responses;

use App\Services\LoginService;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse as FortifyTFLoginResponse;

class TwoFactorLoginResponse extends FortifyTFLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        }

        return redirect()->intended(LoginService::redirectPathFor($request->user()));
    }
}
