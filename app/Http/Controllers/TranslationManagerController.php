<?php

namespace App\Http\Controllers;

use App\Entities\Caches\TranslationCache;
use App\Exports\TranslationsExport;
use App\Http\Requests\{
    TranslationImportRequest,
    TranslationRequest,
};
use App\Imports\TranslationsImport;
use App\Traits\FlashNotifiable;
use App\Services\{
    TranslationManagerService,
    TranslationService
};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Excel;

class TranslationManagerController extends Controller
{
    use FlashNotifiable;

    private $baseRouteName ="admin.settings.translation-manager";

    private $translationCache;
    private $translationManagerService;

    public function __construct(
        TranslationCache $translationCache,
        TranslationManagerService $translationManagerService
    ) {
        $this->translationCache = $translationCache;
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
        if ($translation) {
            $this->translationManagerCache->flushGroup(
                $translation['locale'],
                $translation['group']
            );
        }

        $this->generateFlashMessage('Translation updated successfully!');

        return redirect()->back();
    }

    private function getExportFileName(
        string $locale,
        string $group = null
    ): string {

        $template = "translation-??-?.csv";

        return Str::replaceArray(
            '?',
            [
                $locale,
                ($group ? '-'.$group : ''),
                date('YmdHis'),
            ],
            $template
        );
    }

    public function export(string $locale, string $group = null)
    {
        $translationExport = new TranslationsExport($locale, $group);

        return $translationExport->download(
            $this->getExportFileName($locale, $group),
            Excel::CSV,
            ['Content-Type' => 'text/csv']
        );
    }

    public function import(TranslationImportRequest $request)
    {
        $translationImport = new TranslationsImport();

        $translationImport->import(
            $request->file('file'),
            null,
            Excel::CSV
        );

        $this->translationCache->flush();

        $this->generateFlashMessage('Translation imported successfully!');

        return redirect()->back();
    }
}
