<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageBuilderComponentUserListRequest;
use App\Models\User;
use App\Services\CountryService;
use App\Services\GlobalOptionService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ApiPageBuilderComponentUserListController extends Controller
{
    private $metaKeys = ['country'];
    private $perPage = 12;

    public function __construct()
    {
        $this->metaKeys = array_merge(
            $this->metaKeys,
            $this->additionalMetaKeys()
        );
    }

    public function __invoke(PageBuilderComponentUserListRequest $request)
    {
        $metaKeys = $this->metaKeys;

        $country = $request->get('country');
        $defaultCountries = $request->get('default_countries');
        $defaultTypes = $request->get('default_types');
        $excludedId = $request->get('excluded_user');
        $orderBy = $request->get('order_by');
        $roles = $request->get('roles');
        $term = $request->get('term');
        $type = $request->get('type');

        if (
            ($orderBy === 'random' && $term)
            || $orderBy !== 'random'
            || $country
            || $type
        ) {
            session()->put('randomMod', $this->getRandomPrimeNumber());
        }

        $users = User::select([
                'id',
                'unique_key',
                'first_name',
                'last_name',
                'profile_photo_media_id',
            ])
            ->available()
            ->when($roles, function ($q, $roles) {
                $this->inRoles($q, $roles);
            })
            ->when($excludedId, function ($q, $excludedId) {
                $this->excludeUser($q, $excludedId);
            })
            ->when($orderBy, function($q, $orderBy) {
                $this->orderBy($q, $orderBy);
            })
            ->when($defaultCountries, function ($q, $defaultCountries) {
                $this->inCountries($q, $defaultCountries);
            })
            ->when($defaultTypes, function($q, $defaultTypes) {
                $this->inTypes($q, $defaultTypes);
            })
            ->when($term, function($q, $term) {
                $this->searchName($q, $term);
            })
            ->hasPermissionNames(['public_page.profile'])
            ->with([
                'metas' => function ($q) use ($metaKeys) {
                    $q->whereIn('key', $metaKeys);
                },
                'profilePhoto' => function ($q) {
                    $q->select([
                        'id',
                        'extension',
                        'file_name',
                        'file_url',
                        'version',
                    ]);
                },
                'roles' => function ($q) {
                    $q->select(['id', 'name', 'guard_name']);
                    $q->with([
                        'permissions' => function ($q) {
                            $q->select(['id', 'name', 'guard_name']);
                        },
                    ]);
                },
            ])
            ->get();

        $this->mapRecords($users, $metaKeys);

        return [
            'options' => [
                'countries' => $this->countryOptions($users, [
                    'type' => $type
                ]),
                'types' => $this->typeOptions($users, [
                    'country' => $country
                ]),
            ],
            'users' => $users
                ->when($country, function ($collection) use ($country) {
                    return $collection->where('country', $country);
                })
                ->when($type, function ($collection) use ($type) {
                    return $collection->where('discipline', $type);
                })
                ->values()
                ->paginate($this->perPage),
        ];
    }

    private function mapRecords(EloquentCollection &$users): void
    {
        $metaKeys = $this->metaKeys;

        $users = $users->map(function ($user) use ($metaKeys) {

            $metas = collect($metaKeys)->mapWithKeys(function ($metaKey) use ($user) {
                $value = $user->metas->pluck('value', 'key')->get($metaKey);
                return [ $metaKey => $value ];
            })->all();

            return array_merge(
                [
                    'full_name' => $user->fullName,
                    'profile_photo_url' => (
                        $user->optimizedProfilePhotoUrl
                        ?? config('constants.profile_photo_path')
                    ),
                    'profile_page_url' => (
                        $user->hasPublicPage
                        ? $user->profilePageUrl
                        : null
                    ),
                ],
                $metas,
            );
        });
    }

    private function inRoles($q, string $roles)
    {
        $roleIds = Crypt::decrypt($roles);

        $q->inRoles($roleIds);
    }

    private function excludeUser($q, string $excludedId)
    {
        $excludedId = Crypt::decryptString($excludedId);
        $excludedId = preg_replace('/[^0-9,]+/', '', $excludedId);

        $excludedIds = array_filter(explode(',', $excludedId));

        if ($excludedIds) {
            $q->whereNotIn('id', $excludedIds);
        }
    }

    private function orderBy($q, string $orderBy)
    {
        if ($orderBy == 'random') {
            $randomMod = session()->get('randomMod');

            if (! $randomMod) {
                $randomMod = $this->getRandomPrimeNumber();

                session()->put('randomMod', $randomMod);
            }

            $q->orderByRaw('CAST(unique_key as integer) % ' . $randomMod);
        } elseif ($orderBy == 'first_name-asc') {
            $q->orderBy('first_name', 'ASC');
        } elseif ($orderBy == 'first_name-desc') {
            $q->orderBy('first_name', 'DESC');
        } elseif ($orderBy == 'created_at-asc') {
            $q->orderBy('created_at', 'ASC');
        }
    }

    private function inCountries($q, array $countries)
    {
        $q->whereHas('metas', function ($q) use ($countries) {
            $q->where('key', 'country');
            $q->whereIn('value', $countries);
        });
    }

    private function inTypes($q, array $types)
    {
        $q->whereHas('metas', function ($q) use ($types) {
            $q->where('key', 'discipline');
            $q->whereIn('value', $types);
        });
    }

    private function searchName($q, string $term)
    {
        $q->where(function ($q) use ($term) {
                $q->where(DB::raw("CONCAT(first_name,' ',last_name)"), 'ILIKE', '%'.$term.'%');
            })
            ->orWhere(function ($q) use ($term) {
                $q->whereHas('metas', function ($q) use ($term) {
                    $q->where('key', 'stage_name');
                    $q->where('value', 'ILIKE', '%'.$term.'%');
                });
            });
    }

    private function additionalMetaKeys(): array
    {
        return [
            'discipline',
            'stage_name',
        ];
    }

    private function countryOptions(Collection $users, array $options): array
    {
        $countries = app(CountryService::class)->getUserCountryOptions();
        $availableCountries = $users
            ->when($options['type'], function ($collection) use ($options) {
                return $collection->where('discipline', $options['type']);
            })
            ->pluck('country')
            ->all();

        return $countries
            ->whereIn('id', $availableCountries)
            ->all();
    }

    private function typeOptions(Collection $users, array $options): array
    {
        $types = app(GlobalOptionService::class)->getUserDisciplineOptions();
        $availableTypes = $users
            ->when($options['country'], function ($collection) use ($options) {
                return $collection->where('country', $options['country']);
            })
            ->pluck('discipline')
            ->all();

        return $types
            ->whereIn('id', $availableTypes)
            ->all();
    }

    private function getRandomPrimeNumber(int $min = 2, int $max = 11): int
    {
        $primeNumber = [];

        while ($min <= $max) {
            $divCount=0;

            for ($i=1; $i <= $min; $i++)
            {
                if (($min % $i) == 0) {
                    $divCount++;
                }
            }

            if ($divCount < 3) {
                $primeNumber[] = $min;
            }

            $min = $min + 1;
        }

        return $primeNumber[array_rand($primeNumber)];
    }
}
