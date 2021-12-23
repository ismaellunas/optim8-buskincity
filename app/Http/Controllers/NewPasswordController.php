<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\NewPasswordController as FortifyNewPasswordController;

class NewPasswordController extends FortifyNewPasswordController
{
    public function store(Request $request): Responsable
    {
        $request->validate(
            [
                Fortify::email() => [
                    Rule::exists('users')->where(function ($query) {
                        return $query->where('is_suspended', false);
                    }),
                ],
            ],
            [
                Fortify::email().'.exists' => __('Your Account is suspended, please contact the support.'),
            ]
        );

        return parent::store($request);
    }
}
