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

    public function getMeta(string $key, string $locale = null): mixed
    {
        $meta = $this->user->metas->firstWhere('key', $key);

        if (is_null($meta)) {
            return null;
        }

        if (!is_null($locale)) {
            return $meta->value[$locale] ?? null;
        }

        return $meta->value;
    }
}
