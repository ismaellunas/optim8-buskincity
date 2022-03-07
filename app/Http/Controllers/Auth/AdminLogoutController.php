<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

class AdminLogoutController extends AuthenticatedSessionController
{
    public function logout(Request $request)
    {
        $this->guard->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(Fortify::redirects('admin_login', '/'));
    }
}
