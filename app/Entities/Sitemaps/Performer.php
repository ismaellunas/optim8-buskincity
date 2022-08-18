<?php

namespace App\Entities\Sitemaps;

use App\Contracts\SitemapInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Performer extends BaseSitemap implements SitemapInterface
{
    public function urls(): array|Collection
    {
        $urls = $this->getEloquentBuilder()
            ->get()
            ->map(function ($user) {
                return $this->createUrlTag($user);
            })
            ->sortBy('loc');

        return $urls;
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $latestUser = $this->getEloquentBuilder()
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($latestUser) {
            $lastmod = $latestUser->first()->updated_at;
        }

        return array_merge(
            parent::optionalTags(),
            [
                'lastmod' => $lastmod,
            ]
        );
    }

    private function getEloquentBuilder(): Builder
    {
        return User::select([
                'first_name',
                'last_name',
                'unique_key',
                'updated_at',
            ])
            ->available()
            ->inRoleNames([config('permission.role_names.performer')]);
    }

    private function createUrlTag(User $user): UrlTag
    {
        return new UrlTag(
            $this->locationUrl($user->profilePageUrl),
            ['lastmod' => $user->updated_at]
        );
    }
}
