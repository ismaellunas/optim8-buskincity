<?php

namespace Modules\Space\Services;

use App\Models\City;
use App\Models\User;
use App\Services\CountryService;
use App\Services\GlobalOptionService;
use App\Services\LegacyLandingNavFilter;
use App\Services\MenuService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Kalnoy\Nestedset\Collection as NestedSetCollection;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;

class SpaceService
{
    private function conditionsBuilder(
        ?array $ids = null,
        array $scopes = null,
    ): Builder {
        $spaces = null;

        // null  = no id restriction (global admin)
        // []    = scoped user with no visible spaces — must not fall through to "show all"
        if ($ids !== null) {
            if ($ids === []) {
                return Space::whereRaw('1 = 0');
            }

            $spaces = Space::select('id', '_lft', '_rgt')->whereIn('id', $ids)->get();
        }

        return Space::when($spaces, function ($query, $spaces) {
                foreach ($spaces as $key => $space) {
                    $boolean = $key == 0 ? 'and' : 'or';
                    $query->whereDescendantOrSelf($space, $boolean);
                }
            })
            ->when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    $query->$scopeName($value);
                }
            });
    }

    public function getRecords(
        Authenticatable $user,
        ?array $ids = null,
        array $scopes = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $columnNames = ['id', 'name', 'parent_id', 'type_id', '_lft', '_rgt'];

        $records = $this
            ->conditionsBuilder($ids, $scopes)
            ->select($columnNames)
            ->withDepth()
            ->with([
                'ancestors' => function ($query) use ($columnNames) {
                    $query->select($columnNames);
                    $query->defaultOrder();
                    $query->withDepth();
                    $query->with('type:id,name');
                },
                'type:id,name',
            ])
            ->defaultOrder()
            ->paginate($perPage);

        $records->getCollection()->transform(function ($space) use ($user) {
            $space->makeHidden('type');

            $space->ancestorNames = $space
                   ->ancestors
                   ->sortBy('depth')
                   ->pluck('name')
                   ->all();

            $space->typeName = $space->type->name ?? null;
            $space->can = [
                'edit' => $user->can('update', $space) || $user->can('managePage', $space),
                'delete' => $user->can('delete', $space),
            ];

            return $space;
        });

        return $records;
    }

    public function parentOptions(
        ?Collection $managedSpaces = null,
        string $label = null,
        ?array $ignoreIds = null,
    ): Collection {
        $builder = Space::select(['id', 'name', 'parent_id', '_lft', '_rgt'])
            ->withDepth()
            ->when($ignoreIds, function ($query, $ignoreIds) {
                $query->whereNotIn('id', $ignoreIds);
            });

        if (is_countable($managedSpaces)) {
            if ($managedSpaces->isEmpty()) {
                $builder->whereRaw('1 = 0');
            } else {
                foreach ($managedSpaces as $key => $id) {
                    $boolean = $key == 0 ? 'and' : 'or';
                    $builder->whereDescendantOrSelf($id, $boolean);
                }
            }
        }

        $options = $builder->get()
            ->filter(fn ($space) => $space->isParentable)
            ->map(function ($space) {
                return [
                    'id' => $space->id,
                    'value' => $space->name,
                    'depth' => $space->depth,
                ];
            })
            ->sortBy([
                ['depth', 'asc'],
                ['value', 'asc']
            ])
            ->values();

        if ($label) {
            $options->prepend(['id' => null, 'value' => $label, 'depth' => -1]);
        }

        return $options;
    }

    public function parentOptionsFor(
        Space $space,
        ?Collection $managedSpaces = null,
        ?string $label = null
    ): Collection {
        if (is_null($space->depth)) {
            $space = Space::withDepth()->find($space->id);
        }

        $leafDepth = $space->descendants()
            ->whereIsLeaf()
            ->withDepth()
            ->get(['id', 'parent_id', '_lft', '_rgt'])
            ->max('depth') ?? $space->depth;

        $maxAvailableDepth = ModuleService::maxParentDepth() - ($leafDepth - $space->depth + 1);

        $options = $this->parentOptions(
            $managedSpaces,
            $label,
            $space->descendants->pluck('id')->push($space->id)->all()
        );

        return $options->filter(function ($option) use ($maxAvailableDepth, $space) {
            return (
                $option['id'] == $space->parent_id
                || $option['depth'] <= $maxAvailableDepth
            );
        });
    }

    public function managers(
        string $term = null,
        array $excludedIds = [],
        int $limit = 15
    ): Collection {

        return User::available()
            ->backend()
            ->notInRoleNames([config('permission.super_admin_role')])
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->when($excludedIds, function ($query, $excludedIds) {
                $query->whereNotIn('id', $excludedIds);
            })
            ->limit($limit)
            ->get([
                'id',
                'first_name',
                'last_name',
            ])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'value' => $user->fullName,
                ];
            });
    }

    public function types(): Collection
    {
        return app(GlobalOptionService::class)->getOptionByType(
            config('space.type_option')
        );
    }

    public function typeOptions(?string $noneLabel = null): Collection
    {
        $options = collect();

        if (!is_null($noneLabel)) {
            $options->push(['id' => null, 'value' => $noneLabel]);
        }

        foreach ($this->types() as $type) {
            $options->push(['id' => $type->id, 'value' => __($type->name)]);
        }

        return $options;
    }

    /**
     * Get parent options for City Administrator users.
     * Returns only City-type Spaces that match the user's administered cities.
     */
    public function cityAdminParentOptions(User $user): Collection
    {
        if ($user->isSpecialEventsAdmin()) {
            $adminCityIds = collect(
                $user->scopeIdsFor(config('permission.role_names.special_events_admin'), 'city')
            );
        } else {
            $adminCityIds = collect(
                app(\App\Services\UserScopeService::class)->scopedCityIds($user)
            );
        }
        
        // Get the "City" type ID
        $cityType = $this->types()->firstWhere('name', 'City');
        
        if (!$cityType || $adminCityIds->isEmpty()) {
            return collect();
        }

        return Space::select(['id', 'name as value', 'city_id', 'country_code', 'latitude', 'longitude'])
            ->where('type_id', $cityType->id)
            ->whereIn('city_id', $adminCityIds)
            ->orderBy('name')
            ->get()
            ->map(function ($space) {
                $latitude = $space->latitude;
                $longitude = $space->longitude;

                if (($latitude === null || $longitude === null) && $space->city_id) {
                    $city = \App\Models\City::find($space->city_id);
                    $latitude ??= $city?->latitude;
                    $longitude ??= $city?->longitude;
                }

                return [
                    'id' => $space->id,
                    'value' => $space->value,
                    'city_id' => $space->city_id,
                    'country_code' => $space->country_code,
                    'city_name' => $space->value,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            });
    }

    /**
     * Get type options for City Administrator users.
     * Returns only Pitch and Special Events / Festivals types.
     */
    public function cityAdminTypeOptions(): Collection
    {
        $allowedTypes = ['Pitch', 'Special Events / Festivals'];
        
        return $this->types()
            ->filter(fn($type) => in_array($type->name, $allowedTypes))
            ->map(fn($type) => ['id' => $type->id, 'value' => __($type->name)])
            ->values();
    }

    /**
     * Persist `city_id` on a Space from explicit input or inherited parent (T3.3).
     */
    public function persistCityId(Space $space, array $inputs): void
    {
        $cityId = $inputs['city_id'] ?? null;

        if (! $cityId && ! empty($space->parent_id)) {
            $parent = Space::find($space->parent_id);
            $cityId = $parent?->city_id;
        }

        if ($cityId && (int) $space->city_id !== (int) $cityId) {
            $space->city_id = (int) $cityId;
            $space->save();
        }
    }

    public function ensureCitySpacesExist(User $user): void
    {
        $cityType = $this->types()->firstWhere('name', 'City');

        if (! $cityType) {
            return;
        }

        $cities = $user->assignedScopeCities();

        if ($cities->isEmpty()) {
            $cities = $user->adminCities;
        }

        foreach ($cities as $city) {
            $existing = Space::where('type_id', $cityType->id)
                ->where(function ($query) use ($city) {
                    $query->where('city_id', $city->id)
                        ->orWhere(function ($query) use ($city) {
                            $query->whereNull('city_id')
                                ->whereRaw('LOWER(name) = ?', [mb_strtolower($city->name)]);
                        });
                })
                ->first();

            if ($existing) {
                $this->linkCitySpaceToCity($existing, $city);

                continue;
            }

            Space::create([
                'name' => $city->name,
                'type_id' => $cityType->id,
                'city_id' => $city->id,
                'country_code' => $city->country_code,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'is_page_enabled' => false,
            ]);
        }
    }

    /**
     * Link legacy City-type space nodes to scoped cities and return their IDs.
     *
     * @param  array<int, int>  $scopedCityIds
     * @return array<int, int>
     */
    public function scopedCitySpaceIds(array $scopedCityIds): array
    {
        if ($scopedCityIds === []) {
            return [];
        }

        $cityType = $this->types()->firstWhere('name', 'City');

        if (! $cityType) {
            return [];
        }

        $cities = \App\Models\City::whereIn('id', $scopedCityIds)->get(['id', 'name', 'country_code']);

        $spaceIds = [];

        foreach ($cities as $city) {
            $space = Space::where('type_id', $cityType->id)
                ->where(function ($query) use ($city) {
                    $query->where('city_id', $city->id)
                        ->orWhere(function ($query) use ($city) {
                            $query->whereNull('city_id')
                                ->whereRaw('LOWER(name) = ?', [mb_strtolower($city->name)]);
                        });
                })
                ->first();

            if ($space) {
                $this->linkCitySpaceToCity($space, $city);
                $spaceIds[] = (int) $space->id;
            }
        }

        return array_values(array_unique($spaceIds));
    }

    private function linkCitySpaceToCity(Space $space, \App\Models\City $city): void
    {
        $dirty = false;

        if ((int) $space->city_id !== (int) $city->id) {
            $space->city_id = (int) $city->id;
            $dirty = true;
        }

        if (! $space->country_code && $city->country_code) {
            $space->country_code = $city->country_code;
            $dirty = true;
        }

        if ($dirty) {
            $space->save();
        }
    }

    /**
     * Ensure the city Space node exists and apply application branding (Phase 5).
     *
     * @param  array{logo_media_id?: int|null, cover_media_id?: int|null, description?: string|null, excerpt?: string|null}  $branding
     */
    public function provisionCitySpaceForApplication(
        User $user,
        int $cityId,
        array $branding = [],
        ?int $countrySpaceId = null
    ): Space {
        $this->ensureCitySpacesExist($user);

        $cityType = $this->types()->firstWhere('name', 'City');

        if (! $cityType) {
            throw new \RuntimeException('City space type is not configured.');
        }

        $space = Space::where('type_id', $cityType->id)
            ->where('city_id', $cityId)
            ->firstOrFail();

        $countrySpace = $countrySpaceId
            ? $this->findCountrySpace($countrySpaceId)
            : $this->findCountrySpaceForCity(City::find($cityId));

        if ($countrySpace) {
            $this->nestCitySpaceUnderCountry($space, $countrySpace);
        }

        $this->applyCitySpaceBranding($space, $branding);

        return $space;
    }

    /**
     * Country-type Spaces available for role applications (header-nav eligible).
     *
     * @return array<int, array{id: int, name: string, country_code: string|null}>
     */
    public function getApplicationCountrySpaceOptions(): array
    {
        $countryTypeId = $this->types()->firstWhere('name', 'Country')?->id;

        if (! $countryTypeId) {
            return [];
        }

        $countryService = app(CountryService::class);

        return Space::query()
            ->where('type_id', $countryTypeId)
            ->orderBy('name')
            ->get()
            ->filter(fn (Space $space) => LegacyLandingNavFilter::isNavigableCountrySpace($space))
            ->map(fn (Space $space) => [
                'id' => (int) $space->id,
                'name' => $space->name,
                'country_code' => $countryService->toAlpha2($space->country_code),
            ])
            ->values()
            ->all();
    }

    public function findCountrySpace(int $countrySpaceId): ?Space
    {
        $countryTypeId = $this->types()->firstWhere('name', 'Country')?->id;

        if (! $countryTypeId) {
            return null;
        }

        $space = Space::query()->find($countrySpaceId);

        if (! $space || (int) $space->type_id !== (int) $countryTypeId) {
            return null;
        }

        if (! LegacyLandingNavFilter::isNavigableCountrySpace($space)) {
            return null;
        }

        return $space;
    }

    public function findCountrySpaceForCity(?City $city): ?Space
    {
        if (! $city || blank($city->country_code)) {
            return null;
        }

        $countryTypeId = $this->types()->firstWhere('name', 'Country')?->id;

        if (! $countryTypeId) {
            return null;
        }

        $alpha2 = app(CountryService::class)->toAlpha2($city->country_code);
        $alpha3 = app(CountryService::class)->toAlpha3($city->country_code);

        return Space::query()
            ->where('type_id', $countryTypeId)
            ->where(function ($query) use ($alpha2, $alpha3, $city) {
                if ($alpha2) {
                    $query->orWhere('country_code', $alpha2);
                }

                if ($alpha3) {
                    $query->orWhere('country_code', $alpha3);
                }

                $query->orWhereRaw('LOWER(name) = ?', [strtolower(trim($city->country_code))]);
            })
            ->get()
            ->first(fn (Space $space) => LegacyLandingNavFilter::isNavigableCountrySpace($space));
    }

    public function nestCitySpaceUnderCountry(Space $citySpace, Space $countrySpace): void
    {
        $cityTypeId = $this->types()->firstWhere('name', 'City')?->id;
        $countryTypeId = $this->types()->firstWhere('name', 'Country')?->id;

        if (! $cityTypeId || ! $countryTypeId) {
            return;
        }

        if ((int) $citySpace->type_id !== (int) $cityTypeId) {
            throw new \InvalidArgumentException('Only city spaces can be nested under a country.');
        }

        if ((int) $countrySpace->type_id !== (int) $countryTypeId) {
            throw new \InvalidArgumentException('Parent must be a country space.');
        }

        if ((int) $citySpace->parent_id === (int) $countrySpace->id) {
            return;
        }

        $citySpace->parent_id = $countrySpace->id;

        if (! $citySpace->country_code && $countrySpace->country_code) {
            $citySpace->country_code = $countrySpace->country_code;
        }

        $citySpace->save();
    }

    /**
     * @param  array{logo_media_id?: int|null, cover_media_id?: int|null, description?: string|null, excerpt?: string|null}  $branding
     */
    public function applyCitySpaceBranding(Space $space, array $branding): void
    {
        if (! empty($branding['logo_media_id'])) {
            $this->replaceLogo($space, (int) $branding['logo_media_id']);
        }

        if (! empty($branding['cover_media_id'])) {
            $this->replaceCover($space, (int) $branding['cover_media_id']);
        }

        $locale = defaultLocale();
        $dirty = false;

        if (! empty($branding['description'])) {
            $space->translateOrNew($locale)->description = $branding['description'];
            $dirty = true;
        }

        if (! empty($branding['excerpt'])) {
            $space->translateOrNew($locale)->excerpt = $branding['excerpt'];
            $dirty = true;
        }

        if ($dirty) {
            $space->save();
        }
    }

    public function formattedManagers(Space $space): Collection
    {
        return $space->managers->map(function ($manager) {
            return [
                'id' => $manager->id,
                'value' => $manager->fullName,
            ];
        });
    }

    public function editableRecord(?Space $space = null): array
    {
        if (is_null($space)) {
            $space = new Space();
        }

        $spaceData = $space->only(array_merge(
            ['id'],
            $space->getFillable()
        ));

        if (! $space->contacts) {
            $spaceData['contacts'] = [];
        }

        $spaceData['managers'] = $space->managers;
        $spaceData['city_relation'] = $space->city;

        if ($space->translations->isEmpty()) {
            $space->translateOrNew();
            $spaceData['translations'] = $space->getTranslationsArray();
        } else {
            $spaceData['translations'] = $space->getTranslationsArray();
        }

        return $spaceData;
    }

    private function detachLogo(Space $space): void
    {
        $logoMediaId = $space->logo->id ?? null;

        if ($logoMediaId) {
            $space->detachMedia($logoMediaId);
        }
    }

    private function detachCover(Space $space): void
    {
        $coverMediaId = $space->cover->id ?? null;

        if ($coverMediaId) {
            $space->detachMedia($coverMediaId);
        }
    }

    public function replaceLogo(Space $space, ?int $mediaId = null): void
    {
        $this->detachLogo($space);

        if ($mediaId) {
            $space->media()->attach($mediaId, [
                'type' => Space::TYPE_LOGO
            ]);
        }
    }

    public function replaceCover(Space $space, ?int $mediaId = null): void
    {
        $this->detachCover($space);

        if ($mediaId) {
            $space->media()->attach($mediaId, [
                'type' => Space::TYPE_COVER
            ]);
        }
    }

    public function removeAllMedia(array $spaces): void
    {
        foreach ($spaces as $space) {
            $this->detachLogo($space);
            $this->detachCover($space);
        }
    }

    public function removeAllPages(array $spaces): void
    {
        foreach ($spaces as $space) {
            if ($space->page) {
                $space->page->delete();
            }
        }
    }

    public function removeAllMenus(array $spaces): void
    {
        foreach ($spaces as $space) {
            app(MenuService::class)->removeModelFromMenus($space);
        }
    }

    public function getTopParents(): NestedSetCollection
    {
        return Space::topParent()
            ->select([
                'id',
                'name',
                'page_id',
                'parent_id',
                'is_page_enabled',
            ])
            ->withStructuredUrl([currentLocale(), defaultLocale()])
            ->with([
                'translations',
                'logoMedia' => function ($query) {
                    $query->select([
                        'extension',
                        'file_name',
                        'file_url',
                        'version',
                    ]);
                },
            ])
            ->orderBy('name', 'asc')
            ->get();
    }

    public function pageFormRecord(Space $space): Page
    {
        $page = $space->page;

        $page->translations->transform(function ($translation) {

            $translation->append('landingPageSpaceUrl');

            $translation->setVisible([
                'id',
                'locale',
                'title',
                'excerpt',
                'slug',
                'meta_title',
                'meta_description',
                'status',
                'landingPageSpaceUrl',
            ]);

            return $translation;
        });

        return $page;
    }

    public function totalSpaceByType(Authenticatable $user, int $typeId): int
    {
        $spaceIds = null;
        $scopes = [];

        if ($user->hasRole('city_administrator')) {
            // Get spaces they manage
            $managedSpaceIds = $user->spaces->pluck('id')->all();
            
            // Get spaces from their cities
            $cityIds = $user->adminCities->pluck('id')->toArray();
            $citySpaceIds = Space::whereIn('city_id', $cityIds)
                ->pluck('id')
                ->all();
            
            // Combine both sets
            $spaceIds = array_unique(array_merge($managedSpaceIds, $citySpaceIds));
        } elseif (! $user->can('space.viewAny')) {
            $spaceIds = $user->spaces->pluck('id')->all();
        }

        return $this->conditionsBuilder($spaceIds, array_merge(
            ['inType' => [$typeId]],
            $scopes
        ))->count();
    }
}
