<?php

namespace App\Services;

use App\Models\User;

class UserProfileService
{
    private $user;

    public function __construct()
    {
        $userId = request()->route()->parameter('id');
        $this->user = User::find($userId);
    }

    public function getMeta(string $key): mixed
    {
        $meta = $this->user->metas->firstWhere('key', $key);

        return $meta ? $meta->value : null;
    }
}
