<?php

namespace App\Entities\Sitemaps;

use App\Models\Post as PostModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Post extends BaseSitemap
{
    public function urls(): array|Collection
    {
        return $this->getModel()->map(function ($post) {
                return $this->createUrlTag($post);
            });
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $posts = $this->getModel()->sortByDesc('updated_at');

        if (!$posts->isEmpty()) {
            $lastmod = $posts->first()->updated_at;
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
        return PostModel::inLanguages([$this->locale])
            ->published()
            ->orderBy('slug')
            ->get([
                'slug',
                'updated_at',
            ]);
    }

    private function createUrlTag(PostModel $post): UrlTag
    {
        return new UrlTag(
            $this->locationUrl(route('blog.show', [$post->slug])),
            ['lastmod' => $post->updated_at]
        );
    }
}
