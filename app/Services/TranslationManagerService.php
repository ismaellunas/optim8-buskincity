<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TranslationManagerService
{
    private $defaultLocale = 'en';

    public function getRecords(
        string $locale = null,
        string $group = null,
        int $perPage = 15
    ): LengthAwarePaginator{
        if (!$locale) {
            $locale = TranslationService::getDefaultLocale();
        }

        return $this->getTranslationByLocale($locale)
            ->when($group, function ($collection) use ($group) {
                return $collection->where('group', $group);
            })
            ->paginate($perPage)
            ->withQueryString();
    }

    private function getAllKeyWithGroups(): array
    {
        return Translation::select('key', 'group')
            ->whereIn('group', config('constants.translations.groups'))
            ->groupBy('key', 'group')
            ->pluck('group', 'key')
            ->toArray();
    }

    private function getAllTranslations(): array
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
            ->get()
            ->toArray();
    }

    private function getTranslationByLocale(string $locale): Collection
    {
        $translations = collect([]);
        $allKeyWithGroups = collect($this->getAllKeyWithGroups());
        $allTranslations = collect($this->getAllTranslations());
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
}