<?php

namespace App\Http\Controllers;

use App\Entities\Caches\TranslationCache;
use App\Http\Requests\TranslationRequest;
use App\Models\Translation;
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
        $inputs = $request->validated();

        $translation = Translation::updateOrCreate(
            [
                "id" => $request->id
            ],
            $inputs
        );

        $this->translationManagerCache->flushGroup(
            $translation->locale,
            $translation->group
        );

        $this->generateFlashMessage('Translation updated successfully!');

        return redirect()->back();
    }

    public function clear(Translation $translation)
    {
        $translation->value = null;
        $translation->save();

        $this->generateFlashMessage('Translation cleared successfully!');

        return redirect()->back();
    }
}
