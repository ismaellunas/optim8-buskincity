<?php

namespace App\Http\Controllers;

use App\Entities\Caches\TranslationCache;
use App\Exports\TranslationsExport;
use App\Http\Requests\{
    TranslationExportRequest,
    TranslationImportRequest,
    TranslationManagerRequest,
    TranslationUpdateRequest,
    TranslationStoreRequest,
};
use App\Imports\TranslationsImport;
use App\Models\Translation;
use App\Services\{
    TranslationManagerService,
    TranslationService
};
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Excel;

class TranslationManagerController extends CrudController
{
    protected $title = 'Translation Manager';
    protected $baseRouteName ="admin.settings.translation-manager";

    private $componentName = "TranslationManager/";

    private $translationCache;
    private $translationManagerService;
    private $translationService;
    private $referenceLocale;

    public function __construct(
        TranslationCache $translationCache,
        TranslationManagerService $translationManagerService,
        TranslationService $translationService
    ) {
        $this->translationCache = $translationCache;
        $this->translationManagerService = $translationManagerService;
        $this->translationService = $translationService;

        $this->referenceLocale = $this->translationManagerService->getReferenceLocale();
    }

    public function create()
    {
        return Inertia::render(
            $this->componentName . 'Create',
            $this->getData([
                'breadcrumbs' => [
                    [
                        'title' => $this->getIndexTitle(),
                        'url' => route($this->baseRouteName.'.edit'),
                    ],
                    [
                        'title' => $this->getCreateTitle(),
                    ],
                ],
                'referenceLocale' => $this->referenceLocale,
                'groupOptions' => config('constants.translations.groups'),
                'title' => $this->getCreateTitle(),
                'i18n' => [
                    'key' => __('Key'),
                    'value' => __('Value'),
                    'create' => __('Create'),
                    'cancel' => __('Cancel'),
                ],
            ])
        );
    }

    public function store(TranslationStoreRequest $request)
    {
        $inputs = $request->validated();

        foreach ($inputs['value'] as $locale => $value) {
            if ($value) {
                $translation = new Translation();
                $data = [
                    'locale' => $locale,
                    'key' => $inputs['key'],
                    'value' => $value,
                ];

                $translation->saveFromInputs($data);

                $this->translationCache->flushStringGroup(
                    $locale
                );
            }
        }

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __('Translation')
        ]);

        return redirect()->route($this->baseRouteName . '.edit');
    }

    public function edit(TranslationManagerRequest $request)
    {
        $defaultLocale = $this->translationService->getDefaultLocale();

        return Inertia::render(
            $this->componentName . 'Index',
            $this->getData([
                'defaultLocale' => $defaultLocale,
                'referenceLocale' => $this->referenceLocale,
                'groupOptions' => config('constants.translations.groups'),
                'localeOptions' => collect($this->translationService->getLocaleOptions())
                    ->sortBy('name', SORT_NATURAL)
                    ->values()
                    ->all(),
                'pageQueryParams' => array_filter(
                    $request->only('locale', 'groups', 'term')
                ),
                'bags' => [
                    'import' => 'translationImport',
                ],
                'records' => $this->translationManagerService->getRecords(
                    $request->locale,
                    $request->groups,
                    $request->term,
                    $this->recordsPerPage,
                ),
                'instructions' => [
                    'fileInputNotes' => [
                        __('Accepted file extension: :extensions', [
                            'extensions' => implode(',', config('constants.extensions.import'))
                        ]),
                        __('Max file size: :size', ['size' => '5MB']),
                    ]
                ],
                'i18n' => [
                    'export' => __('Export'),
                    'import' => __('Import'),
                    'add_new' => __('Add new'),
                    'update' => __('Update'),
                    'search' => __('Search'),
                    'group' => __('Group'),
                    'key' => __('Key'),
                    'value' => __('Value'),
                    'actions' => __('Actions'),
                    'file' => __('File'),
                    'submit' => __('Submit'),
                    'cancel' => __('Cancel'),
                ],
            ])
        );
    }

    public function update(TranslationUpdateRequest $request)
    {
        $translations = $request->translations;

        $this->translationManagerService->batchUpdate($translations);

        $translation = collect($translations)->first();

        if ($translation) {
            $this->translationCache->flushLocale(
                $translation['locale'],
            );
        }

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Translation')
        ]);

        return redirect()->back();
    }

    public function destroy(Translation $translation)
    {
        $translations = Translation::where('key', $translation->key)->get();

        foreach ($translations as $translation) {
            $locale = $translation->locale;

            $translation->delete();

            $this->translationCache->flushStringGroup(
                $locale
            );
        }

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Translation')
        ]);

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

        $this->generateFlashMessage('The :resource was imported!', [
            'resource' => __('Translation')
        ]);

        $failures = $translationImport->failures();

        $redirect = redirect()->back();

        if ($failures->isNotEmpty()) {
            $redirect->withErrors($failures->toArray());
        }

        return $redirect;
    }
}
