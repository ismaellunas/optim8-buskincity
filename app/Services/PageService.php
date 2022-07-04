<?php

namespace App\Services;

use App\Contracts\PageBuilderComponentInterface;
use App\Helpers\Url;
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

                if ($class instanceof PageBuilderComponentInterface) {
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

    public static function generateUniqueKey()
    {
        $isCodeExist = function ($code) {
            return PageTranslation::where('unique_key', $code)->exists();
        };

        return Url::randomDigitSegment($isCodeExist);
    }
}
