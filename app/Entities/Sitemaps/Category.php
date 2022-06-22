<?php

namespace App\Entities\Sitemaps;

use App\Models\Category as CategoryModel;
use App\Models\CategoryTranslation;
use App\Models\Post;
use App\Services\TranslationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Category extends BaseSitemap
{
    public function urls(): array|Collection
    {
        return CategoryModel::select([
                'id',
                'updated_at',
            ])
            ->with([
                'translations' => function ($query) {
                    $table = CategoryTranslation::getTableName();

                    $query
                        ->select([
                            "$table.category_id",
                            "$table.locale",
                            "$table.slug",
                            "$table.updated_at",
                        ])
                        ->orderBy("$table.updated_at", 'asc');
                },

                'posts' => function ($query) {
                    $table = Post::getTableName();

                    $query
                        ->select([
                            "$table.updated_at",
                        ])
                        ->published()
                        ->orderBy("$table.updated_at", 'desc')
                        ->limit(1);
                }
            ])
            ->get()
            ->map(function ($category) {
                return $this->createUrlTag($category);
            })
            ->sortBy('loc');
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $latestCategoryTranslation = $this->getCategoryTranslation($this->locale)
            ?? $this->getCategoryTranslation(TranslationService::getDefaultLocale())
            ?? $this->getCategoryTranslation();

        if ($latestCategoryTranslation) {
            $lastmod = $latestCategoryTranslation->updated_at;
            $updatedAtPost = $latestCategoryTranslation->category->posts->last()->updated_at
                ?? null;

            if ($updatedAtPost && $updatedAtPost->gt($lastmod)) {
                $lastmod = $updatedAtPost;
            }
        }

        return array_merge(
            parent::optionalTags(),
            [
                'lastmod' => $lastmod,
            ]
        );
    }

    private function getCategoryTranslation(string $locale = null): ?CategoryTranslation
    {
        return CategoryTranslation::select([
                'category_id',
                'updated_at',
            ])
            ->with([
                'category.posts' => function ($query) {
                    $table = Post::getTableName();

                    $query
                        ->select([
                            "$table.updated_at",
                        ])
                        ->published()
                        ->orderBy("$table.updated_at", 'desc')
                        ->limit(1);
                }
            ])
            ->when($locale, function ($query) use ($locale) {
                $query->inLanguages([$locale]);
            })
            ->orderBy('updated_at', 'desc')
            ->first();
    }

    private function createUrlTag(CategoryModel $category): UrlTag
    {
        $lastmod = $category->updated_at;
        $updatedAtTranslation = $category->translations->first()->updated_at ?? null;

        if ($updatedAtTranslation && $updatedAtTranslation->gt($lastmod)) {
            $lastmod = $updatedAtTranslation;
        }

        $updatedAtPost = $category->posts->last()->updated_at ?? null;

        if ($updatedAtPost && $updatedAtPost->gt($lastmod)) {
            $lastmod = $updatedAtPost;
        }

        return new UrlTag(
            $this->locationUrl(
                route('blog.category.index', [
                    $category->slug ?? $category->translations->first()->slug
                ])
            ),
            [
                'lastmod' => $lastmod
            ]
        );
    }
}
