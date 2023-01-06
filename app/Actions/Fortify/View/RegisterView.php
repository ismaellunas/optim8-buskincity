<?php

namespace App\Actions\Fortify\View;

class RegisterView
{
    public function __invoke()
    {
        return view('auth.register');
    }
}