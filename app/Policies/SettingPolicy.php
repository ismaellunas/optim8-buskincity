<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy
{
    use HandlesAuthorization;

    public function manageKeys(User $user)
    {
        return (
            $user->isSuperAdministrator
            || $user->isAdministrator
        );
    }
}
