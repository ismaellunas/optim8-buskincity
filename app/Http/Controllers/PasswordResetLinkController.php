<?php

namespace App\Http\Controllers;

use App\Rules\SuspendedUserEmail;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController as FortifyPasswordResetLinkController;

class PasswordResetLinkController extends FortifyPasswordResetLinkController
{
    public function store(Request $request): Responsable
    {
        $request->validate(
            [
                Fortify::email() => new SuspendedUserEmail(),
            ],
        );

        return parent::store($request);
    }
}
