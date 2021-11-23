<?php

namespace App\Actions;

use App\Models\User;
use App\Services\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateLoginAttempt
{
    public function __invoke(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (
            $user
            && LoginService::isAdminLoginAttemptRoute($request->route())
            && !$user->can('system.dashboard')
        ) {
            $user = null;
        }

        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }
    }
}
