<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GlobalOptionPolicy extends BasePermissionPolicy
{
    public function viewAny(User $user)
    {
        return (
            $user->isSuperAdministrator || $user->isAdministrator
        );
    }

    public function create(User $user)
    {
        return (
            $user->isSuperAdministrator || $user->isAdministrator
        );
    }

    public function update(User $user, Model $model)
    {
        return (
            $user->isSuperAdministrator || $user->isAdministrator
        );
    }

    public function delete(User $user, Model $model)
    {
        return (
            $user->isSuperAdministrator || $user->isAdministrator
        );
    }
}
