<?php

namespace App\Http\Controllers;

use App\Rules\SuspendedUserEmail;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\NewPasswordController as FortifyNewPasswordController;

class NewPasswordController extends FortifyNewPasswordController
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
