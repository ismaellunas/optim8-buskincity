<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Builder;

class PostService
{
    public function getRecords(
        string $term = null,
        array $scopeNames = [],
        int $recordsPerPage = 15
    ) {
        $query = Post::orderBy('id', 'DESC')
            ->with([
                'coverImage' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'id',
                        'version',
                    ]);
                },
                'categories' => function ($query) {
                    $tableName = Category::getTableName();
                    $query->select([$tableName.'.id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query
                                ->select('id', 'name', 'category_id', 'locale')
                                ->where('locale', TranslationService::getDefaultLocale());
                        },
                    ]);
                },
            ])
            ->when($term, function (Builder $query, $term) {
                $query->where('title', 'ILIKE', '%'.$term.'%');
                $query->orWhere('content', 'ILIKE', '%'.$term.'%');
                $query->orWhere('excerpt', 'ILIKE', '%'.$term.'%');
                $query->orWhere('slug', 'ILIKE', '%'.$term.'%');
            });

        foreach ($scopeNames as $scopeName => $value) {
            if (is_int($scopeName)) {
                $query->{$value}();
            } else {
                $query->{$scopeName}($value);
            }
        }

        $records = $query->paginate($recordsPerPage);

        $this->transformRecords($records);

        return $records;
    }

    public function getBlogRecords(
        string $term,
        int $recordsPerPage = 10,
        string $locale = 'en'
    ) {
        $records = Post::orderBy('id', 'DESC')
            ->where('locale', $locale)
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->published()
            ->with([
                'coverImage' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'id',
                        'version',
                    ]);
                },
                'categories' => function ($query) {
                    $tableName = Category::getTableName();
                    $query->select([$tableName.'.id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('id', 'name', 'category_id', 'locale');
                        },
                    ]);
                },
            ])
            ->paginate($recordsPerPage);

        $this->transformRecords($records);

        return $records;
    }

    public function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            $record->thumbnail_url = (
                $record->coverImage
                ? $record->coverImage->thumbnailUrl
                : null
            );

            return $record;
        });
    }

    public function getCategoryOptions(): array
    {
        return Category::get()->map(function ($category) {
            return [
                'id' => $category->id,
                'value' => $category->name,
            ];
        })->all();
    }

    public static function isSlugExists(
        string $slug,
        array $excludedIds = null
    ): bool {
        return Post::where('slug', $slug)
            ->when($excludedIds, function ($query) use ($excludedIds) {
                $query->where('id', '<>', $excludedIds);
            })
            ->exists();
    }

    public static function getUniqueSlug(
        string $slug,
        array $excludedIds = null
    ): string {
        if (self::isSlugExists($slug, $excludedIds)) {
            $slug = self::getUniqueSlug(
                $slug.'-'.date('ymdHis'),
                $excludedIds
            );
        }
        return $slug;
    }

    public function getFirstBySlug(
        string $slug,
        string $locale
    ): ?Post {
        return Post::where('slug', $slug)
            ->published()
            ->where('locale', $locale)
            ->first();
    }
}
