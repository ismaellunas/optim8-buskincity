<?php

namespace App\Models;

use App\Entities\Caches\TranslationCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Spatie\TranslationLoader\TranslationLoaders\TranslationLoader;

class Translation extends Model implements TranslationLoader
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'locale',
        'module',
        'value',
    ];

    public function saveFromInputs(array $inputs)
    {
        $this->fill($inputs);
        $this->save();
    }

    // Method from TranslationLoader-Interface
    public function loadTranslations(string $locale, string $group): array
    {
        $translationCache = app(TranslationCache::class);

        try {
            return $translationCache->rememberForGroup(
                $locale,
                function () use ($locale, $group) {
                    return self::select([
                        'locale',
                        'group',
                        'key',
                        'value',
                    ])
                    ->where('locale', $locale)
                    ->when($group, function ($query) use ($group) {
                        if ($group !== "*") {
                            return $query->where('group', $group);
                        }
                    })
                    ->get()
                    ->pluck('value', 'key')
                    ->toArray();
                },
                $group
            );
        } catch (QueryException $e) {
            if (in_array($e->getCode(), ["42P01", "2002"])) {
                return [];
            }
        }
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeLocales($query, mixed $locales)
    {
        return $query->whereIn('locale', $locales);
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeGroups($query, mixed $groups)
    {
        foreach ($groups as $group) {
            if ($group == 'no_group') {
                $query = $query->whereNull('group');
            }
        }

        return $query->orWhere(function ($q) use ($groups) {
            $q->whereIn('group', $groups);
        });
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('key', 'ILIKE', '%'.$term.'%')
            ->orWhere('value', 'ILIKE', '%'.$term.'%');
    }
}
