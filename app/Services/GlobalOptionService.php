<?php

namespace App\Services;

use App\Models\GlobalOption;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\HasCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GlobalOptionService
{
    use HasCache;

    public function getRecords(
        string $term = null,
        array $scopeNames = [],
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = GlobalOption::orderBy('name', 'ASC')
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%');
            });

        foreach ($scopeNames as $scopeName => $value) {
            if (is_int($scopeName)) {
                $query->{$value}();
            } else {
                $query->{$scopeName}($value);
            }
        }

        return $query->paginate($perPage);
    }

    public function getOptionByType(string $type): Collection
    {
        return GlobalOption::type($type)->get();
    }
}