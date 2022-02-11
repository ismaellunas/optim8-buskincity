<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $role = $user->getRoleNames()->first();

        $viewName = 'profile-'.Str::kebab($role);

        if (view()->exists($viewName)) {
            return view($viewName, []);
        }

        return view('profile');
    }
}
