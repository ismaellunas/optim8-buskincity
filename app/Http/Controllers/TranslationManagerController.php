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
    private $translationService;

    public function __construct(
        TranslationCache $translationCache,
        TranslationManagerService $translationManagerService,
        TranslationService $translationService
    ) {
        $this->translationManagerCache = $translationCache;
        $this->translationManagerService = $translationManagerService;
        $this->translationService = $translationService;
    }

    public function edit(Request $request)
    {
        $defaultLocale = $this->translationService->getDefaultLocale();

        return Inertia::render('TranslationManager', [
            'title' => 'Translation Manager',
            'baseRouteName' => $this->baseRouteName,
            'defaultLocale' => $defaultLocale,
            'referenceLocale' => 'en',
            'groupOptions' => config('constants.translations.groups'),
            'localeOptions' => $this->translationService->getLocaleOptions(),
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
        $groups = collect($translations)
            ->groupBy('group')
            ->keys();

        if ($translation) {
            $groups->each(function ($group) use ($translation) {
                $this->translationManagerCache->flushGroup(
                    $translation['locale'],
                    $group
                );
            });
        }

        $this->generateFlashMessage('Translation updated successfully!');

        return redirect()->back();
    }
}
