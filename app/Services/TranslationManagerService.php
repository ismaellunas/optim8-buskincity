<?php

namespace App\Services;

use App\Entities\Caches\TranslationCache;
use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class TranslationManagerService
{
    public $defaultLocale = 'en';
    private $translationManagerCache;

    public function __construct(
        TranslationCache $translationCache,
    ) {
        $this->translationManagerCache = $translationCache;
    }

    public function getRecords(
        string $locale = null,
        string $group = null,
        int $perPage = 15
    ): LengthAwarePaginator{
        if (!$locale) {
            $locale = TranslationService::getDefaultLocale();
        }

        return $this->paginateCollection(
            $this->getTranslationByLocale($locale)
                ->when($group, function ($collection) use ($group) {
                    return $collection->where('group', $group);
                }),
            $perPage
        );
    }

    public function sycn(array $translations): void
    {
        foreach ($translations as $translation) {
            $newTranslation = Translation::updateOrCreate(
                [
                    "id" => $translation['id']
                ],
                $translation
            );

            $this->translationManagerCache->flushGroup(
                $newTranslation->locale,
                $newTranslation->group
            );
        }
    }

    private function getAllKeyWithGroups(): Collection
    {
        return Translation::select('key', 'group')
            ->whereIn('group', config('constants.translations.groups'))
            ->groupBy('key', 'group')
            ->pluck('group', 'key');
    }

    private function getAllTranslations(): Collection
    {
        return Translation::select(
                'id',
                'locale',
                'group',
                'key',
                'value',
            )
            ->orderBy('id', 'DESC')
            ->active()
            ->get();
    }

    private function getTranslationByLocale(string $locale): Collection
    {
        $translations = collect([]);
        $allKeyWithGroups = $this->getAllKeyWithGroups();
        $allTranslations = $this->getAllTranslations();
        $allKeyWithGroups->each(function ($group, $key) use (
                $translations,
                $allTranslations,
                $locale
            ) {
                $translation = $allTranslations->where('group', $group)
                    ->where('key', $key)
                    ->where('locale', $locale)
                    ->first();

                $defaultTranslation = $allTranslations->where('group', $group)
                    ->where('key', $key)
                    ->where('locale', $this->defaultLocale)
                    ->first();

                if (!$translation) {
                    $translation = [
                        'id' => null,
                        'locale' => $locale,
                        'group' => $group,
                        'key' => $key,
                        'en_value' => $defaultTranslation['value'] ?? null,
                        'value' => null,
                    ];
                } else {
                    $translation['en_value'] = $defaultTranslation['value'] ?? null;
                }

                $translations->push($translation);
            });

        return $translations;
    }

    private function paginateCollection(
        Collection $items,
        int $perPage = 15
    ): LengthAwarePaginator {
        $page = Paginator::resolveCurrentPage('page');
        $total = $items->count();

        return new Paginator(
            $items->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
}