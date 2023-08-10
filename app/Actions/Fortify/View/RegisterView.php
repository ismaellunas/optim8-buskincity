<?php

namespace App\Actions\Fortify\View;

use App\Services\LoginService;

class RegisterView
{
    public function __invoke()
    {
        return view('auth.register', [
            'availableSocialiteDrivers' => LoginService::getAvailableSocialiteDrivers(),
            'isSocialiteDriverExists' => LoginService::isSocialiteDriverExists(),
        ]);
    }
}