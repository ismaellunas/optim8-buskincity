<?php

namespace App\Services;

use App\Contracts\PageBuilderSearchableTextInterface;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Services\{
    SettingService,
};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageService
{
    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = Page::orderBy('id', 'DESC')
            ->select(['id'])
            ->with([
                'translations' => function ($q) {
                    $q->select(['id', 'page_id', 'slug', 'title', 'meta_description', 'meta_title', 'status', 'locale']);
                },
            ])
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    private static function getModuleEntityClassName(string $componentName): ?string
    {
        foreach (app(ModuleService::class)->getAllEnabledNames() as $moduleName) {
            $className = '\\Modules\\'.$moduleName.'\\Entities\\PageBuilderComponents\\' . $componentName;

            if (class_exists($className)) {
                return $className;
            }
        }

        return null;
    }

    public static function getEntityClassName(string $componentName): ?string
    {
        $className = '\\App\\Entities\\PageBuilderComponents\\' . $componentName;

        if (class_exists($className)) {
            return $className;
        }

        return self::getModuleEntityClassName($componentName);
    }

    public static function transformComponentToText($data): string
    {
        $string = "";

        foreach ($data['entities'] as $entity) {

            $className = self::getEntityClassName($entity['componentName']);

            if ($className) {
                $class = new $className($entity);

                if ($class instanceof PageBuilderSearchableTextInterface) {
                    $string .= $class->getText() . ' ';
                }
            }

            continue;
        }

        return trim($string);
    }

    public function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            $record->title = $record->title ?? $record->translations[0]->title;
            $record->slug = $record->slug ?? $record->translations[0]->slug;
            $record->status = $record->status ?? $record->translations[0]->status;
            $record->meta_title = $record->meta_title ?? $record->translations[0]->meta_title;
            $record->meta_description = $record->meta_description ?? $record->translations[0]->meta_description;
            $record->setAppends([
                'availableTranslations',
                'hasMetaDescription',
                'hasMetaTitle',
                'statusText',
                'urlDefaultLocale',
            ]);

            return $record;
        });
    }

    public function getHomePage(): ?Page
    {
        $settingService = app(SettingService::class);

        $homePageId = $settingService->getHomePage();

        if ($homePageId === "") {
            return null;
        }

        return Page::with([
                'translations' => function ($query) {
                    $query->published();
                },
            ])
            ->where('id', $homePageId)
            ->first();
    }

    public function getImagesFromPage(Page $page): array
    {
        $images = [];

        foreach ($page->translations as $translation) {
            if (!empty($translation->data['media'])) {
                $locale = $translation->locale;
                $mediaIds = collect($translation->data['media'])->pluck('id');

                $images[$locale] = Media::whereIn('id', $mediaIds)
                    ->image()
                    ->with([
                        'translations' => function ($q) use ($locale) {
                            $q->select(['id', 'locale', 'alt', 'media_id']);
                            $q->where('locale', $locale);
                        },
                    ])
                    ->default()
                    ->get(['id', 'version', 'file_name', 'extension'])
                    ->transform(function ($image) {
                        $image->file_url = $image->optimizedImageUrl;

                        return $image;
                    })
                    ->all();
            }
        }

        return $images;
    }

    public static function getUniqueSlug(string $slug): string
    {
        if (self::isSlugExists($slug)) {
            $slug = self::getUniqueSlug(
                $slug.'-'.date('ymdHis')
            );
        }

        return $slug;
    }

    private static function isSlugExists(string $slug): bool
    {
        return PageTranslation::where('slug', $slug)->exists();
    }

    public function filterInputs(array $inputs): array
    {
        return collect($inputs)
            ->filter(function ($input) {
                return !empty($input);
            })
            ->all();
    }

    public function getPageOptions(?string $placeholder = null): array
    {
        $options = [];

        if ($placeholder) {
            $options[] = [
                'id' => null,
                'value' => $placeholder,
                'locale' => null,
            ];
        }

        return [
            ...$options,
            ...Page::with([
                'translations' => function ($query) {
                    $query->select([
                        'id',
                        'page_id',
                        'locale',
                        'title',
                    ])
                    ->published();
                },
            ])
            ->get(['id'])
            ->map(function ($page) {
                if (count($page->translations) !== 0) {
                    $locales = $page
                        ->translations
                        ->map(function ($translation) {
                            return $translation->locale;
                        });
                    return [
                        'id' => $page->id,
                        'value' => $page->title ?? $page->translations[0]->title,
                        'locales' => $locales,
                    ];
                }
            })
            ->filter()
            ->sortBy('value')
            ->values()
            ->all()
        ];
    }
}
