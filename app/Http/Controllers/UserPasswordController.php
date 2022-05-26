<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;
use JoelButcher\Socialstream\Http\Controllers\Inertia\PasswordController;

class UserPasswordController extends PasswordController
{
    use AuthorizesRequests;

    public function store(Request $request, SetsUserPasswords $setter)
    {
        $this->authorize('setPassword', [User::class, auth()->user()]);

        return parent::store($request, $setter);
    }
}
