<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'user';

    public function delete(User $user, Model $selectedUser)
    {
        return (
            parent::delete($user, $selectedUser)
            && !$selectedUser->isSuperAdministrator
            && $user->id != $selectedUser->id
        );
    }
}
