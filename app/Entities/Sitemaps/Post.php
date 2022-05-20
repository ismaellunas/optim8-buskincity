<?php

namespace App\Entities\Sitemaps;

use App\Models\Post as PostModel;
use Illuminate\Support\Collection;

class Post extends BaseSitemap
{
    public function urls(): array|Collection
    {
        return PostModel::inLanguages([$this->locale])
            ->published()
            ->orderBy('slug')
            ->get([
                'slug',
                'updated_at',
            ])
            ->map(function ($page) {
                return $this->createUrlTag($page);
            });
    }

    private function createUrlTag(PostModel $post): UrlTag
    {
        return new UrlTag(
            $this->locationUrl(route('blog.show', [$post->slug])),
            ['lastmod' => $post->updated_at]
        );
    }
}
