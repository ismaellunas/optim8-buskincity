<?php

namespace App\Http\Controllers;

use App\Services\LoginService;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController as FortifyTwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

class TwoFactorAuthenticatedSessionController extends FortifyTwoFactorAuthenticatedSessionController
{
    /**
     * @override
     */
    public function store(TwoFactorLoginRequest $request)
    {
        $response = parent::store($request);

        if (auth()->check()) {
            LoginService::setHomeUrl($request);
        }

        return $response;
    }
}
