<?php

namespace App\Http\Controllers;

use App\Entities\Caches\SettingCache;
use App\Http\Requests\LanguageRequest;
use App\Services\LanguageService;
use App\Traits\FlashNotifiable;
use Inertia\Inertia;

class LanguageController extends Controller
{
    use FlashNotifiable;

    private $languageService;
    private $baseRouteName = 'admin.settings.languages';

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function edit()
    {
        return Inertia::render('Language', [
            'title' => __('Languages'),
            'baseRouteName' => $this->baseRouteName,
            'supportedLanguages' => $this->languageService->getSupportedLanguageIds(),
            'defaultLanguage' => $this->languageService->getDefaultId(),
            'languageOptions' => $this->languageService->getShownLanguageOptions(),
            'i18n' => [
                'default_language' => __('Default language'),
                'supported_languages' => __('Supported languages'),
                'update' => __('Update'),
            ],
        ]);
    }

    public function update(LanguageRequest $request)
    {
        $languageIds = $request->languages;

        $this->languageService->sync($languageIds);

        $this->languageService->setDefault($request->default_language);

        app(SettingCache::class)->flush();

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Language')
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
