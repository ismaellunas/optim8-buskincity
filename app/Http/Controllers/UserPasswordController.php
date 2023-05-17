<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\PasswordValidationWithoutConfirmationRules;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;
use JoelButcher\Socialstream\Http\Controllers\Inertia\PasswordController;

class UserPasswordController extends PasswordController
{
    use AuthorizesRequests;
    use PasswordValidationWithoutConfirmationRules;

    public function store(Request $request, SetsUserPasswords $setter): RedirectResponse
    {
        $this->authorize('setPassword', [User::class, auth()->user()]);

        Validator::make($request->only(['password', 'password_confirmation']), [
            'password' => $this->passwordRules(),
            'password_confirmation' => ['required', 'string'],
        ])->validateWithBag('setPassword');

        return parent::store($request, $setter);
    }
}
