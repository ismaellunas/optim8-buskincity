<?php

namespace App\Entities\Sitemaps;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Performer extends BaseSitemap
{
    public function urls(): array|Collection
    {
        $urls = $this->getModel()->map(function ($user) {
                return $this->createUrlTag($user);
            })
            ->sortBy('loc');

        return $urls;
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $users = $this->getModel()->sortByDesc('updated_at');

        if (!$users->isEmpty()) {
            $lastmod = $users->first()->updated_at;
        }

        return array_merge(
            parent::optionalTags(),
            [
                'lastmod' => $lastmod,
            ]
        );
    }

    private function getModel(): Collection
    {
        return User::
            available()
            ->inRoleNames([config('permission.role_names.performer')])
            ->get([
                'id',
                'first_name',
                'last_name',
                'unique_key',
                'updated_at',
            ]);
    }

    private function createUrlTag(User $user): UrlTag
    {
        return new UrlTag(
            $this->locationUrl($user->profilePageUrl),
            ['lastmod' => $user->updated_at]
        );
    }
}
