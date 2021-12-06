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
