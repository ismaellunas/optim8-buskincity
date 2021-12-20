<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Setting;
use Illuminate\Support\Collection;

class LanguageService
{
    public function getShownLanguageOptions(): Collection
    {
        return Language::shown()
            ->get(['id', 'name'])
            ->asOptions('id', 'name');
    }

    public function sync(array $languageIds): void
    {
        $supportedLanguageIds = Language::active()->pluck('id');

        $deactivatedLanguageIds = $supportedLanguageIds->diff($languageIds);

        Language::whereIn('id', $languageIds)
            ->update([
                'is_active' => true,
            ]);

        Language::whereIn('id', $deactivatedLanguageIds)
            ->update([
                'is_active' => false,
            ]);
    }

    public function getDefault(): ?Language
    {
        $defaultId = Setting::key('default_language')->value('value');
        return $defaultId ? Language::find($defaultId) : null;
    }

    public function getDefaultId(): ?int
    {
        return $this->getDefault()->id ?? null;
    }

    public function setDefault(int $defaultLanguageId)
    {
        $defaultLanguage = Setting::firstOrCreate([
            'key' => 'default_language',
        ]);

        $defaultLanguage->value = $defaultLanguageId;

        $defaultLanguage->save();
    }

    public function getSupportedLanguages(): Collection
    {
        return Language::active()->get();
    }

    public function getSupportedLanguageIds(): array
    {
        return $this->getSupportedLanguages()->pluck('id')->all();
    }

}
