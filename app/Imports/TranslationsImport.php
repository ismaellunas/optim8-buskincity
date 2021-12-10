<?php

namespace App\Imports;

use App\Rules\GroupAndKeyTranslation;
use App\Services\{
    TranslationManagerService,
    TranslationService
};
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\{
    Importable,
    SkipsFailures,
    SkipsOnFailure,
    ToCollection,
    WithHeadingRow,
    WithValidation,
};

class TranslationsImport implements ToCollection, WithValidation, WithHeadingRow, SkipsOnFailure
{
    use Importable;
    use SkipsFailures;

    private $groupKeys;

    private $translationManagerService;
    private $translationService;

    public function __construct()
    {
        $this->translationManagerService = app(TranslationManagerService::class);
        $this->translationService = app(TranslationService::class);
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $translations)
    {
        foreach ($translations as $translation) {
            $this->translationManagerService->saveTranslation(
                $translation['key'],
                $translation['value'],
                $translation['locale'],
                $translation['group'],
                true,
            );
        }
    }

    public function rules(): array
    {
        $keyWithGroups = $this
            ->translationManagerService
            ->getAllKeyWithGroups()
            ->mapToGroups(function ($group, $key) {
                return [$group => $key];
            });

        return [
            'key' => [new GroupAndKeyTranslation($keyWithGroups)],
            'locale' => Rule::in($this->translationService->getLocales()),
            'value' => ['max:1024'],
        ];
    }
}
