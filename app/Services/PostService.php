<?php

namespace App\Services;

use App\Models\{
    Category,
    Language,
    Post,
};
use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PostService
{
    public function getRecords(
        string $term = null,
        array $scopeNames = [],
        int $recordsPerPage = 15
    ) {
        $query = Post::orderBy('id', 'DESC')
            ->with([
                'media' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'version',
                    ]);
                },
                'categories' => function ($query) {
                    $tableName = Category::getTableName();
                    $query->select([$tableName.'.id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query
                                ->select('id', 'name', 'category_id', 'locale');
                        },
                    ]);
                },
            ])
            ->when($term, function (Builder $query, $term) {
                $query->search($term);
            })
            ->select([
                'id',
                'locale',
                'title',
                'slug',
                'excerpt',
                'status',
                'cover_image_id',
            ]);

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
        string $locale = 'en',
        ?int $categoryId = null
    ) {
        $records = Post::orderBy('id', 'DESC')
            ->where('locale', $locale)
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->byCategory($categoryId);
            })
            ->published()
            ->with([
                'media' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'version',
                    ]);
                },
                'categories' => function ($query) {
                    $tableName = Category::getTableName();
                    $query->select([$tableName.'.id']);
                    $query->with([
                        'translations' => function ($query) {
                            $query->select('id', 'name', 'slug', 'category_id', 'locale');
                        },
                    ]);
                },
            ])
            ->select([
                'id',
                'locale',
                'title',
                'slug',
                'excerpt',
                'status',
                'cover_image_id',
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

            $record->categories->transform(function ($category) {
                $category->name = $category->name ?? $category->translations[0]->name;

                return $category;
            });

            $record->category_names = $record->getCategoryNames();

            return $record;
        });
    }

    public function getCategoryOptions(): array
    {
        return Category::get()->map(function ($category) {
            return [
                'id' => $category->id,
                'value' => $category->name ?? $category->translations[0]->name,
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
        string $locale = null
    ): ?Post {
        return Post::where('slug', $slug)
            ->with([
                'author',
                'media' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'version',
                    ]);
                },
            ])
            ->when($locale, function ($query) use ($locale) {
                $query->where('locale', $locale);
            })
            ->first();
    }

    public function getLanguageOptions(Post $post = null): array
    {
        $localeOptions = collect(app(TranslationService::class)->getLocaleOptions());

        if ($post && !$localeOptions->contains('id', $post->locale)) {
            $localeOptions->push(
                $this->getLocale($post->locale)
            );
        }

        return $localeOptions->sortBy('id')
            ->values()
            ->all();
    }

    private function getLocale(string $locale): array
    {
        $language = Language::where('code', $locale)
            ->get([
                'code',
                'name',
            ])
            ->map(function ($item) {
                return [
                    'id' => $item->code,
                    'name' => $item->name
                ];
            })
            ->first();

        return $language ?? [];
    }

    public function readingTime(string $text = ""): float
    {
        $totalWords = str_word_count($text);

        $minutes = ceil($totalWords / config('constants.reading_time_per_minute'));
        $minutes = max(1, $minutes);

        return $minutes;
    }

    public function tableOfContents(string $content = null): Collection
    {
        $tables = collect([]);

        preg_match_all('#<h2.*?>(.*?)</h2>#i',$content, $found);

        if (!empty($found[1])) {
            foreach ($found[1] as $headingText) {
                $strippedHeading = trim((strip_tags($headingText)));

                $tables->push([
                    'tag' => '#' . Str::slug($strippedHeading),
                    'text' => $strippedHeading,
                ]);
            }
        }

        return $tables;
    }

    public function transformContent(string $content = null): ?string
    {
        $tableOfContents = $this->tableOfContents($content);

        $i = 0;
        while (strpos($content, '<h2>') !== false) {
            $headerId = Str::slug($tableOfContents[$i++]['text']);
            $content = preg_replace('/<h2>/', '<h2 id="' . $headerId . '">', $content, 1);
        }

        return $content;
    }

    public function getRelatedArticles(Post $post): mixed
    {
        $categoryId = $post->category->id ?? null;

        if ($categoryId) {
            return Post::select([
                    'id',
                    'title',
                    'slug',
                ])
                ->with([
                    'categories.translations' => function ($q) {
                        $q->select([
                            'id',
                            'name',
                            'locale',
                            'category_id'
                        ]);
                    },
                    'media' => function ($query) {
                        $query->select([
                            'extension',
                            'file_name',
                            'file_url',
                            'version',
                        ]);
                    },
                ])
                ->published()
                ->whereHas('primaryCategories', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                })
                ->whereNotIn('id', [$post->id])
                ->inRandomOrder()
                ->limit(3)
                ->get();
        }

        return collect([]);
    }

    public function getLatestPost(int $limit = 3, array $categoryIds = [])
    {
        return Post::select([
                'id',
                'title',
                'slug',
            ])
            ->with([
                'categories.translations' => function ($q) {
                    $q->select([
                        'id',
                        'name',
                        'locale',
                        'category_id'
                    ]);
                },
                'media' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'version',
                    ]);
                },
            ])
            ->published()
            ->when($categoryIds, function ($q) use ($categoryIds) {
                $q->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                });
            })
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}
