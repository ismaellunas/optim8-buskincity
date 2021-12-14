<?php

namespace App\Http\Controllers;

use App\Entities\Caches\TranslationCache;
use App\Exports\TranslationsExport;
use App\Http\Requests\{
    TranslationExportRequest,
    TranslationImportRequest,
    TranslationManagerRequest,
    TranslationRequest,
};
use App\Imports\TranslationsImport;
use App\Traits\FlashNotifiable;
use App\Services\{
    TranslationManagerService,
    TranslationService
};
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Excel;

class TranslationManagerController extends Controller
{
    use FlashNotifiable;

    private $baseRouteName ="admin.settings.translation-manager";

    private $translationCache;
    private $translationManagerService;
    private $translationService;

    public function __construct(
        TranslationCache $translationCache,
        TranslationManagerService $translationManagerService,
        TranslationService $translationService
    ) {
        $this->translationCache = $translationCache;
        $this->translationManagerService = $translationManagerService;
        $this->translationService = $translationService;
    }

    public function edit(TranslationManagerRequest $request)
    {
        $defaultLocale = $this->translationService->getDefaultLocale();

        return Inertia::render('TranslationManager', [
            'title' => __('Translation Manager'),
            'baseRouteName' => $this->baseRouteName,
            'importRouteName' => 'admin.settings.translation-manager.import',
            'exportRouteName' => 'admin.settings.translation-manager.export',
            'defaultLocale' => $defaultLocale,
            'referenceLocale' => 'en',
            'groupOptions' => config('constants.translations.groups'),
            'localeOptions' => collect($this->translationService->getLocaleOptions())
                ->sortBy('name', SORT_NATURAL)
                ->values()
                ->all(),
            'pageQueryParams' => array_filter($request->only('locale', 'groups')),
            'bags' => [
                'import' => 'translationImport',
            ],
            'records' => $this->translationManagerService->getRecords(
                $request->locale,
                $request->groups
            ),
            'i18n' => [
                'fileInputNotes' => [
                    __('Accepted file extension: :extensions', [
                        'extensions' => implode(',', config('constants.extensions.import'))
                    ]),
                    __('Max file size: :size', ['size' => '5MB']),
                ]
            ]
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
                $this->translationCache->flushGroup(
                    $translation['locale'],
                    $group
                );
            });
        }

        $this->generateFlashMessage('Translation updated successfully!');

        return redirect()->back();
    }

    private function getExportFileName(
        string $locale,
        array $groups = null
    ): string {

        $template = "translation-??-?.csv";

        return Str::replaceArray(
            '?',
            [
                $locale,
                ($groups ? '-'.implode('-', $groups) : ''),
                date('YmdHis'),
            ],
            $template
        );
    }

    public function export(TranslationExportRequest $request, string $locale)
    {
        $translationExport = new TranslationsExport($locale, $request->groups);

        return $translationExport->download(
            $this->getExportFileName($locale, $request->groups),
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

        foreach ($translationImport->getAffectedLocales() as $locale) {
            $this->translationCache->flushLocale($locale);
        }

        $this->generateFlashMessage('Translation imported successfully!');

        $failures = $translationImport->failures();

        $redirect = redirect()->back();

        if ($failures->isNotEmpty()) {
            $redirect->withErrors($failures->toArray());
        }

        return $redirect;
    }
}
