<?php

namespace App\Entities\Sitemaps;

use App\Models\Category as CategoryModel;
use App\Models\CategoryTranslation;
use Illuminate\Support\Collection;

class Category extends BaseSitemap
{
    public function urls(): array|Collection
    {
        $locale = $this->locale;

        return CategoryModel::
            with([
                'translations' => function ($query) use ($locale) {
                    $table = CategoryTranslation::getTableName();

                    $query
                        ->inLanguages([$locale])
                        ->select([
                            "$table.id",
                            "$table.category_id",
                            "$table.locale",
                            "$table.slug",
                            "$table.updated_at",
                        ]);
                },
            ])
            ->get([
                'id',
                'updated_at',
            ])
            ->map(function ($page) {
                return $this->createUrlTag($page);
            });
    }

    private function createUrlTag(CategoryModel $category): UrlTag
    {
        $lastmod = $category->updated_at;
        $updatedAtTranslation = $category->translations->first()->updated_at ?? null;

        if ($updatedAtTranslation && $updatedAtTranslation->gt($lastmod)) {
            $lastmod = $updatedAtTranslation;
        }

        return new UrlTag(
            $this->locationUrl(route('blog.category.index', [$category->id])),
            ['lastmod' => $lastmod]
        );
    }
}
