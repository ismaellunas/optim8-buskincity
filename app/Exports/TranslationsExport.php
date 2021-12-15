<?php

namespace App\Exports;

use App\Services\TranslationManagerService;
use Maatwebsite\Excel\Concerns\{
    Exportable,
    FromCollection,
    WithHeadings,
    WithMapping
};

class TranslationsExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private $locale;
    private $groups;

    public function __construct(string $locale, array $groups = null)
    {
        $this->locale = $locale;
        $this->groups = $groups;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return app(TranslationManagerService::class)
            ->getExportableTranslations(
                [$this->locale],
                $this->groups
            )
            ->when($this->groups, function ($collection) {
                return $collection->whereIn('group', $this->groups);
            })
            ->sortBy([
                ['locale', 'asc'],
                ['group', 'asc'],
            ]);
    }

    public function map($translation): array
    {
        return [
            $translation['locale'],
            $translation['group'],
            $translation['key'],
            $translation['en_value'],
            $translation['value'],
        ];
    }

    public function headings(): array
    {
        return [
            'locale',
            'group',
            'key',
            'english',
            'value',
        ];
    }
}
