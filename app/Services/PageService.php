<?php

namespace App\Services;

use App\Contracts\PageBuilderSearchableTextInterface;
use App\Models\Media;
use App\Models\Page;
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

    public static function getEntityClassName(string $componentName): string
    {
        return '\\App\\Entities\\PageBuilderComponents\\' . $componentName;
    }

    public static function transformComponentToText($data): string
    {
        $string = "";

        foreach ($data['entities'] as $entity) {

            $className = self::getEntityClassName($entity['componentName']);

            if (class_exists($className)) {

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
            $record->setAppends(['statusText', 'hasMetaDescription', 'hasMetaTitle', 'availableTranslations']);

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
                    ->get(['id', 'file_url']);
            }
        }

        return $images;
    }
}
