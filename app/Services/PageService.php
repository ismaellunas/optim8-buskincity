<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageService
{
    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = Page::orderBy('id', 'DESC')
            ->select(['id'])
            ->with([
                'translations' => function ($q) {
                    $q->select(['id', 'page_id', 'slug', 'title', 'meta_description', 'meta_title', 'status', 'locale']);
                },
            ])
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    public static function transformComponentToText($data): string
    {
        $string = "";
        foreach($data['entities'] as $entity)
        {
            $class = '\\App\\Entities\\Components\\' . $entity['componentName'];
            if(class_exists($class)){
                $class = new $class($entity);
                $string .= $class->getText() . ' ';
            }
            continue;
        }

        return trim($string);
    }

    public function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            $record->title = $record->title ?? $record->translations[0]->title;
            $record->slug = $record->slug ?? $record->translations[0]->slug;
            $record->status = $record->status ?? $record->translations[0]->status;
            $record->meta_title = $record->meta_title ?? $record->translations[0]->meta_title;
            $record->meta_description = $record->meta_description ?? $record->translations[0]->meta_description;
            $record->setAppends(['statusText', 'hasMetaDescription', 'hasMetaTitle', 'availableTranslations']);

            return $record;
        });
    }
}
