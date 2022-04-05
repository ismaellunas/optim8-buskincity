<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\SuspendedUserEmail;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController as FortifyPasswordResetLinkController;

class PasswordResetLinkController extends FortifyPasswordResetLinkController
{
    public function store(Request $request): Responsable
    {
        $rules = [
            Fortify::email() => [new SuspendedUserEmail()],
        ];

        if ($request->routeIs('admin.*')) {

            $rules[Fortify::email()][] = function ($attributes, $value, $fail) {

                $isBackendUser = false;

                $user = User::email($value)
                    ->select('id', 'email')
                    ->first();

                if ($user) {
                    $isBackendUser = $user->roles->contains(function ($role) {
                        return $role->hasPermissionTo('system.dashboard');
                    });
                }

                if (! $isBackendUser) {
                    $fail(__('validation.email_belongs_to_backend_user'));
                }
            };
        }

        $request->validate($rules);

        return parent::store($request);
    }
}
