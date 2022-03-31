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

        $groupAndKeys = $this->getGroupAndKeys($groups);

        $records = $this->paginateCollection(
            $groupAndKeys,
            $perPage
        );

        $this->transformRecords($locale, $groups, $records);

        return $records;
    }

    private function transformRecords(
        string $locale = null,
        array $groups = null,
        $records
    ): void {
        $localeWithReferences = $this->getLocaleWithReferences([$locale]);
        $allTranslations = $this->getTranslations($localeWithReferences, $groups);

        $records->getCollection()->transform(
            function ($record) use (
                $allTranslations,
                $locale,
            ) {
                $referenceLocale = $this->getReferenceLocale();

                $translation = null;
                $defaultTranslation = $allTranslations
                    ->where('locale', $this->getReferenceLocale())
                    ->where('group', $record->group)
                    ->firstWhere('key', $record->key);

                if ($locale == $referenceLocale) {

                    $translation = $defaultTranslation;

                } else {
                    $translation = $allTranslations
                        ->where('locale', $locale)
                        ->where('group', $record->group)
                        ->firstWhere('key', $record->key);
                }

                if (!$translation) {
                    $record->id = null;
                    $record->locale = $locale;
                    $record->value = null;
                } else {
                    $record->id = $translation['id'];
                    $record->locale = $locale;
                    $record->value = $translation['value'];
                }

                $record->en_value = $defaultTranslation['value'] ?? null;

                return $record;
            }
        );
    }

    private function getLocaleWithReferences(array $locales): array
    {
        $locales[] = $this->getReferenceLocale();

        return array_unique($locales);
    }

    public function batchUpdate(array $translations): void
    {
        foreach ($translations as $translation) {
            if ($translation['group'] == null) {
                $this->updateRelatedKey($translation);
            } else {
                $this->updateOrCreateTranslation($translation);
            }
        }
    }

    private function updateRelatedKey(array $translation): void
    {
        $oldTranslation = Translation::find($translation['id']);

        if (isset($oldTranslation) && ($oldTranslation['key'] != $translation['key'])) {
            $relatedTranslations = Translation::whereNull('group')
                ->where('key', $oldTranslation['key'])
                ->get();

            foreach ($relatedTranslations as $relatedTranslation) {
                $relatedTranslation->key = $translation['key'];
                $relatedTranslation->save();
            }
        } else {
            $this->updateOrCreateTranslation($translation);
        }
    }

    private function updateOrCreateTranslation(array $translation): void
    {
        Translation::updateOrCreate(
            [
                "id" => $translation['id']
            ],
            $translation
        );
    }

    public function getGroups(): array
    {
        return collect(config('constants.translations.groups'))
            ->keys()
            ->all();
    }

    private function getGroupAndKeys(array $groups = null): Collection
    {
        return Translation::select('key', 'group')
            ->when($groups, function ($query, $groups) {
                $query->groups($groups);
            })
            ->groupBy('key', 'group')
            ->orderBy('group', 'asc')
            ->get();
    }

    public function getGroupedKeys(array $groups = null): Collection
    {
        return $this->getGroupAndKeys($groups)
            ->mapToGroups(function ($translation) {
                return [$translation['group'] => $translation['key']];
            });
    }

    public function getReferenceLocale()
    {
        return 'en';
    }

    private function getTranslations(
        array $locales = null,
        array $groups = null
    ): collection {

        return collect(
            Translation::select(
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
                ->get()
                ->toArray()
        );
    }

    public function getExportableTranslations(
        array $locales,
        array $groups = null
    ): Collection {

        $translations = collect();

        $groupedKeys = $this->getGroupedKeys($groups);
        $localeWithReferences = $this->getLocaleWithReferences($locales);
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

                        $translation = $translation;
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
        string $group = null,
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
