<?php

namespace App\Exports;

use App\Services\TranslationManagerService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TranslationsExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private $locale;
    private $group;

    public function __construct(string $locale, string $group = null)
    {
        $this->locale = $locale;
        $this->group = $group;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return app(TranslationManagerService::class)
            ->getTranslationByLocale($this->locale)
            ->when($this->group, function ($collection) {
                return $collection->where('group', $this->group);
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
