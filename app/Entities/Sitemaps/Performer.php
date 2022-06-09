<?php

namespace App\Entities\Sitemaps;

use App\Models\User;
use Illuminate\Support\Collection;

class Performer extends BaseSitemap
{
    public function urls(): array|Collection
    {
        $urls = User::
            available()
            ->inRoleNames([config('permission.role_names.performer')])
            ->get([
                'id',
                'first_name',
                'last_name',
                'unique_key',
                'updated_at',
            ])
            ->map(function ($user) {
                return $this->createUrlTag($user);
            })
            ->sortBy('loc');

        return $urls;
    }

    private function createUrlTag(User $user): UrlTag
    {
        return new UrlTag(
            $this->locationUrl($user->profilePageUrl),
            ['lastmod' => $user->updated_at]
        );
    }
}
