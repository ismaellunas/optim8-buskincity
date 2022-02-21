<?php

namespace App\Services;

use App\Models\{
    Language,
    Setting
};
use App\Entities\{
    Cookie,
    Caches\SettingCache
};
use App\Services\IPService;
use Illuminate\Support\Collection;

class LanguageService
{
    public function getShownLanguageOptions(): Collection
    {
        $key = config('constants.setting_cache.shown_language_option');

        return app(SettingCache::class)->remember($key, function () {
            return Language::shown()
                ->get(['id', 'name'])
                ->asOptions('id', 'name');
        });
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

    public function getOriginFromIP(): Language
    {
        $userData = app(IPService::class)->getUserData();
        $locale = $userData['location']['language']['code'];

        $language = Language::where('code', $locale)->first();

        return $language ?? $this->getDefault();
    }

    public function getOriginLanguageFromCookie(): string
    {
        $originLanguage = app(Cookie::class)->get('origin_language');

        if (!$originLanguage) {
            $originLanguage = $this->getOriginFromIP()->code;

            app(Cookie::class)->set('origin_language', $originLanguage);
        }

        return $originLanguage;
    }

}
