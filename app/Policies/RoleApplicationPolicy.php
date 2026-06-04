<?php

namespace App\Policies;

use App\Models\RoleApplication;
use App\Models\User;

class RoleApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canReview($user);
    }

    public function view(User $user, RoleApplication $roleApplication): bool
    {
        return $this->canReview($user);
    }

    public function approve(User $user, RoleApplication $roleApplication): bool
    {
        return $this->canReview($user) && $roleApplication->isPending();
    }

    public function reject(User $user, RoleApplication $roleApplication): bool
    {
        return $this->canReview($user) && $roleApplication->isPending();
    }

    private function canReview(User $user): bool
    {
        return $user->hasAnyRole([
            config('permission.role_names.admin'),
            config('permission.super_admin_role'),
        ]);
    }
}
