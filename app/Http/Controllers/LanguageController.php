<?php

namespace App\Http\Controllers;

use App\Services\LanguageService;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LanguageController extends Controller
{
    use FlashNotifiable;

    private $baseRouteName = 'admin.settings.languages';

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function edit()
    {
        return Inertia::render('Language', [
            'title' => __('Language'),
            'baseRouteName' => $this->baseRouteName,
            'supportedLanguages' => $this->languageService->getSupportedLanguageIds(),
            'defaultLanguage' => $this->languageService->getDefaultId(),
            'languageOptions' => $this->languageService->getShownLanguageOptions(),
        ]);
    }

    public function update(Request $request)
    {
        $languageIds = $request->languages;

        $this->languageService->sync($languageIds);

        $this->languageService->setDefault($request->default_language);

        $this->generateFlashMessage('Language updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
