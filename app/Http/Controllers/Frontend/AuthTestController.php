<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\LoginService;

class AuthTestController extends Controller
{
    public function login()
    {
        return view('auth.login-test', [
            'availableSocialiteDrivers' => app(LoginService::class)->getAvailableSocialiteDrivers()
        ]);
    }
}
