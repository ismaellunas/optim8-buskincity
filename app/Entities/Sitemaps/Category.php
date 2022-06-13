<?php

namespace App\Entities\Sitemaps;

use App\Models\Category as CategoryModel;
use App\Models\CategoryTranslation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Category extends BaseSitemap
{
    public function urls(): array|Collection
    {
        return $this->getModel()->map(function ($category) {
                return $this->createUrlTag($category);
            });
    }

    public function optionalTags(): array
    {
        $lastmod = Carbon::today();
        $categories = $this->getModel()->sortByDesc('updated_at');

        if (!$categories->isEmpty()) {
            $lastmod = $categories->first()->updated_at;
            $updatedAtTranslation = $categories->first()->translations->first()->updated_at ?? null;

            if ($updatedAtTranslation && $updatedAtTranslation->gt($lastmod)) {
                $lastmod = $updatedAtTranslation;
            }
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
        return CategoryModel::
            with([
                'translations' => function ($query) {
                    $table = CategoryTranslation::getTableName();

                    $query
                        ->select([
                            "$table.id",
                            "$table.category_id",
                            "$table.locale",
                            "$table.slug",
                            "$table.updated_at",
                        ])
                        ->orderBy("$table.id", 'asc');
                },
            ])
            ->get([
                'id',
                'updated_at',
            ]);
    }

    private function createUrlTag(CategoryModel $category): UrlTag
    {
        $lastmod = $category->updated_at;
        $updatedAtTranslation = $category->translations->first()->updated_at ?? null;

        if ($updatedAtTranslation && $updatedAtTranslation->gt($lastmod)) {
            $lastmod = $updatedAtTranslation;
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
