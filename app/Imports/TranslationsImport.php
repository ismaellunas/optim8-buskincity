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

    private $affectedLocales;

    private $translationManagerService;
    private $translationService;

    public function __construct()
    {
        $this->translationManagerService = app(TranslationManagerService::class);
        $this->translationService = app(TranslationService::class);
    }

    public function getAffectedLocales(): Collection
    {
        return $this->affectedLocales;
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

        $this->affectedLocales = $translations->unique('locale')->pluck('locale');
    }

    public function rules(): array
    {
        $groupedKeys = $this
            ->translationManagerService
            ->getGroupedKeys();

        return [
            'locale' => Rule::in($this->translationService->getLocales()),
            'key' => [new GroupAndKeyTranslation($groupedKeys)],
            'value' => ['max:1024'],
        ];
    }
}
