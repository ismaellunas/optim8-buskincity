<?php

namespace App\Policies;

use App\Models\User;

class ErrorLogPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'error_log';

    public function multipleDelete(User $user)
    {
        return $user->can($this->basePermission.'.delete');
    }
}
