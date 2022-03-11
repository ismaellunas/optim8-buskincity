<?php

namespace App\Services;

use App\Models\User;

class UserProfileService
{
    private User $user;

    public function __construct()
    {
        $user = request()->route()->parameter('user');
        $this->user = $user;
    }

    public function getMeta(string $key): mixed
    {
        $meta = $this->user->metas->firstWhere('key', $key);

        return $meta ? $meta->value : null;
    }
}
