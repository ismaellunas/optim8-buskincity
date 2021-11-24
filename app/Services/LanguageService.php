<?php

namespace App\Services;

use App\Models\Language;

class LanguageService
{
    public function getShownLanguageOptions(): array
    {
        return Language::shown()
            ->get(['id', 'name'])
            ->asOptions('id', 'name')
            ->all();
    }

    public function sync(array $languageIds): void
    {
        $activatedLanguageIds = Language::active()->pluck('id');

        $deactivatedLanguageIds = $activatedLanguageIds->diff($languageIds);

        Language::whereIn('id', $languageIds)
            ->update([
                'is_active' => true,
            ]);

        Language::whereIn('id', $deactivatedLanguageIds)
            ->update([
                'is_active' => false,
            ]);
    }
}
