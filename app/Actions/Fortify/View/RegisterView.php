<?php

namespace App\Actions\Fortify\View;

use App\Services\LoginService;

class RegisterView
{
    public function __invoke()
    {
        return view('auth.register', [
            'isSocialiteDriverExists' => LoginService::isSocialiteDriverExists(),
        ]);
    }
}