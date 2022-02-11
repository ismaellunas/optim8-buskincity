<?php

namespace App\Services;

class UserProfileService
{
    private $user;

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
