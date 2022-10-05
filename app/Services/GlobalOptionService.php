<?php

namespace App\Services;

use App\Entities\Caches\GlobalOptionCache;
use App\Models\GlobalOption;
use App\Models\UserMeta;
use App\Traits\HasCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

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

    public function getOptionByType(string $type): EloquentCollection
    {
        return GlobalOption::type($type)->get();
    }

    public function getDisciplineOptions(): Collection
    {
        $key = 'discipline_options';

        if (! $this->hasLoadedKey($key)) {
            $this->setLoadedKey(
                $key,
                app(GlobalOptionCache::class)
                    ->remember('discipline_options', function () {
                        return $this->getOptionByType('discipline')
                            ->sortBy('name')
                            ->map(function ($discipline) {
                                return [
                                    'id' => $discipline->default_value,
                                    'value' => $discipline->name,
                                ];
                            })
                            ->values();
                    })
            );
        }

        return $this->getLoadedKey($key);
    }

    public function getUserDisciplineOptions(): Collection
    {
        $disciplines = UserMeta::key('discipline')
                ->select('value')
                ->distinct()
                ->pluck('value');

        return $this->getDisciplineOptions()
            ->when($disciplines, function ($q, $disciplines) {
                return $q->whereIn('id', $disciplines);
            })
            ->values();
    }
}