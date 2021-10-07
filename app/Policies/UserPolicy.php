<?php

namespace App\Policies;

class UserPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'user';
}
