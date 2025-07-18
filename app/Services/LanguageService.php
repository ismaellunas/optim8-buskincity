<?php

namespace App\Services;

use App\Models\{
    Language,
    Setting
};
use App\Entities\{
    LifetimeCookie,
    Caches\SettingCache
};
use App\Services\IPService;
use App\Services\TranslationService;
use Illuminate\Support\Collection;

class LanguageService
{
    private $supportedLanguages;
    private $cacheOriginLanguage;

    public function getShownLanguageOptions(): Collection
    {
        $key = config('constants.setting_cache.shown_language_option');

        return app(SettingCache::class)->remember($key, function () {
            return Language::shown()
                ->get(['id', 'name', 'code'])
                ->map(function ($language) {
                    return [
                        'id' => $language->id,
                        'value' => $language->name,
                        'code' => $language->code,
                    ];
                });
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
        if (is_null($this->supportedLanguages)) {
            $key = config('constants.setting_cache.supported_languages');

            $this->supportedLanguages = app(SettingCache::class)->remember(
                $key,
                function () {
                    return Language::active()
                        ->get(['id', 'name', 'code']);
                },
                collect()
            );
        }
        return $this->supportedLanguages;
    }

    public function getSupportedLanguageIds(): array
    {
        return $this->getSupportedLanguages()->pluck('id')->all();
    }

    public function getSupportedLanguageOptions(): Collection
    {
        return $this->getSupportedLanguages()->map(function ($language) {
                return [
                    'id' => $language->id,
                    'value' => $language->name,
                    'code' => $language->code,
                ];
            });
    }

    public function getOriginFromIP(): ?Language
    {
        $locale = app(IPService::class)->getLanguageCode();

        $language = Language::where('code', $locale)->first();

        return $language ?? $this->getDefault();
    }

    public function getOriginLanguageFromCookie(string $defaultLocale = null): string
    {
        $originLanguage = $this->getOriginLanguage();

        if (! $originLanguage) {
            $originLanguage = $defaultLocale ?? $this->getOriginFromIP()->code;
        }

        if (
            ! app(TranslationService::class)->isSupportedLocale($originLanguage)
        ) {
            $originLanguage = defaultLocale();
        }

        $this->setOriginLanguage($originLanguage);

        return $originLanguage;
    }

    public function getOriginLanguage(): ?string
    {
        if (! $this->cacheOriginLanguage) {
            $this->cacheOriginLanguage = app(LifetimeCookie::class)->get('origin_language');
        }

        return $this->cacheOriginLanguage;
    }

    public function setOriginLanguage(string $locale): string
    {
        app(LifetimeCookie::class)->set('origin_language', $locale);

        $this->cacheOriginLanguage = $locale;

        return $locale;
    }
}
