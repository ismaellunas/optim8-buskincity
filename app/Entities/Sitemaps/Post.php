<?php

namespace App\Entities\Sitemaps;

use App\Contracts\SitemapInterface;
use App\Models\Post as PostModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Post extends BaseSitemap implements SitemapInterface
{
    public function urls(): array|Collection
    {
        return $this->getEloquentBuilder()
            ->orderBy('slug')
            ->get()
            ->map(function ($post) {
                return $this->createUrlTag($post);
            });
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $latestPost = $this->getEloquentBuilder()
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($latestPost) {
            $lastmod = $latestPost->updated_at;
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
        return PostModel::select([
                'slug',
                'updated_at',
            ])
            ->inLanguages([$this->locale])
            ->published();
    }

    private function createUrlTag(PostModel $post): UrlTag
    {
        return new UrlTag(
            $this->locationUrl(route('blog.show', [$post->slug])),
            ['lastmod' => $post->updated_at]
        );
    }
}
