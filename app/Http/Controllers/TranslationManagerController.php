<?php

namespace App\Http\Controllers;

use App\Entities\Caches\TranslationCache;
use App\Http\Requests\TranslationRequest;
use App\Traits\FlashNotifiable;
use App\Services\{
    TranslationManagerService,
    TranslationService
};
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranslationManagerController extends Controller
{
    use FlashNotifiable;

    private $baseRouteName ="admin.settings.translation-manager";
    private $translationManagerCache;
    private $translationManagerService;

    public function __construct(
        TranslationCache $translationCache,
        TranslationManagerService $translationManagerService
    ) {
        $this->translationManagerCache = $translationCache;
        $this->translationManagerService = $translationManagerService;
    }

    public function edit(Request $request)
    {
        return Inertia::render('TranslationManager', [
            'title' => 'Translation Manager',
            'baseRouteName' => $this->baseRouteName,
            'defaultLocale' => $this->translationManagerService->defaultLocale,
            'groupOptions' => config('constants.translations.groups'),
            'localeOptions' => TranslationService::getLocaleOptions(),
            'pageQueryParams' => array_filter($request->only('locale', 'group')),
            'records' => $this->translationManagerService->getRecords(
                $request->locale,
                $request->group
            ),
        ]);
    }

    public function update(TranslationRequest $request)
    {
        $translations = $request->translations;

        $this->translationManagerService->batchUpdate($translations);

        $translation = collect($translations)->first();
        $this->translationManagerCache->flushGroup(
            $translation['locale'],
            $translation['group']
        );

        $this->generateFlashMessage('Translation updated successfully!');

        return redirect()->back();
    }
}
