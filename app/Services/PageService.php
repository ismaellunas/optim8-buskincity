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

        $records->getCollection()->transform(function ($record) {
            $record->setAppends(['statusText', 'hasMetaDescription', 'hasMetaTitle']);
            return $record;
        });

        return $records;
    }
}
