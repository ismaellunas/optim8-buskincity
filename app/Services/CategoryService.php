<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Category::orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->whereHas('translations', function ($query) use ($term) {
                    $query->where('name', 'ILIKE', '%'.$term.'%');
                });
            })
            ->with([
                'translations' => function ($query) {
                    $query->select('id', 'name', 'category_id', 'locale');
                },
            ])
            ->paginate($perPage);
    }

    public function getCategoryOptions()
    {
        return Category::with([
                'translations' => function ($q) {
                    $q->select([
                        'id',
                        'name',
                        'locale',
                        'category_id'
                    ]);
                }
            ])
            ->get()
            ->sortBy('name')
            ->map(function ($category) {
                return [
                    'value' => $category->id,
                    'name' => $category->name,
                ];
            });
    }
}
