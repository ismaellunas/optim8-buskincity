<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class TranslationManagerService
{
    public function getRecords(
        string $locale = null,
        array $groups = null,
        int $perPage = 15
    ): LengthAwarePaginator {

        if (!$locale) {
            $locale = TranslationService::getDefaultLocale();
        }

        return $this->paginateCollection(
            $this->getExportableTranslations([$locale], $groups),
            $perPage
        );
    }

    public function batchUpdate(array $translations): void
    {
        foreach ($translations as $translation) {
            Translation::updateOrCreate(
                [
                    "id" => $translation['id']
                ],
                $translation
            );
        }
    }

    public function getGroupedKeys(array $groups = null): Collection
    {
        return Translation::select('key', 'group')
            ->when($groups, function ($query, $groups) {
                $query->groups($groups);
            })
            ->groupBy('key', 'group')
            ->get()
            ->mapToGroups(function ($translation) {
                return [$translation['group'] => $translation['key']];
            });
    }

    private function getReferenceLocale()
    {
        return 'en';
    }

    private function getTranslations(
        array $locales = null,
        array $groups = null
    ): Collection {

        return Translation::select(
                'id',
                'locale',
                'group',
                'key',
                'value',
            )
            ->active()
            ->when($locales, function ($query, $locales) {
                $query->locales($locales);
            })
            ->when($groups, function ($query, $groups) {
                $query->groups($groups);
            })
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function getExportableTranslations(
        array $locales,
        array $groups = null
    ): Collection {

        $translations = collect();

        $groupedKeys = $this->getGroupedKeys($groups);

        $localeWithReferences = $locales;
        array_push($localeWithReferences, $this->getReferenceLocale());
        $localeWithReferences = array_unique($localeWithReferences);

        $allTranslations = $this->getTranslations($localeWithReferences, $groups);

        foreach ($groupedKeys as $group => $keys) {

            foreach ($locales as $locale) {

                foreach ($keys as $key) {

                    $defaultTranslation = $allTranslations
                        ->where('locale', $this->getReferenceLocale())
                        ->where('group', $group)
                        ->firstWhere('key', $key);

                    $translation = [];

                    if ($locale == $this->getReferenceLocale()) {

                        $translation = $defaultTranslation;

                    } else {

                        $translation = $allTranslations
                            ->where('locale', $locale)
                            ->where('group', $group)
                            ->firstWhere('key', $key);
                    }

                    if (!$translation) {

                        $translation = [
                            'id' => null,
                            'locale' => $locale,
                            'group' => $group,
                            'key' => $key,
                            'value' => null,
                        ];

                    } else {

                        $translation = $translation->toArray();
                    }

                    $translation['en_value'] = $defaultTranslation['value'] ?? null;

                    $translations->push($translation);
                }
            }
        }

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

    public function saveTranslation(
        string $key,
        ?string $value,
        string $locale,
        string $group,
        bool $replace = false
    ): bool {
        $translation = Translation::firstOrNew([
            'locale' => $locale,
            'group'  => $group,
            'key'    => $key,
        ]);

        if ($replace || !$translation->value) {
            $translation->value = $value;
        }

        return $translation->save();
    }
}
