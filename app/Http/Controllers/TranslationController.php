<?php

namespace App\Http\Controllers;

use App\Http\Requests\TranslationRequest;
use App\Models\Translation;
use App\Traits\FlashNotifiable;
use App\Services\{
    TranslationManagerService,
    TranslationService
};
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranslationController extends Controller
{
    use FlashNotifiable;

    private $baseRouteName ="admin.settings.translations";
    private $translationManagerService;

    public function __construct(TranslationManagerService $translationManagerService)
    {
        $this->translationManagerService = $translationManagerService;
    }

    public function edit(Request $request)
    {
        return Inertia::render('Translation', [
            'title' => 'Translations',
            'baseRouteName' => $this->baseRouteName,
            'groupOptions' => config('constants.settings.translations.groups'),
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

        Translation::updateOrCreate(
            [
                "id" => $request->id
            ],
            $inputs
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
