<?php

namespace App\Services;

use App\Models\{
    Media,
    User
};
use Illuminate\Support\Collection;

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

    public function getMedias(string $key): Collection
    {
        $mediaIds = $this->getMeta($key);

        if (!empty($mediaIds)) {
            return Media::select([
                    'file_url',
                    'file_type'
                ])
                ->whereIn('id', $mediaIds)
                ->get();
        }

        return collect([]);
    }
}
