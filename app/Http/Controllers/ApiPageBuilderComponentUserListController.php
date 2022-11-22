<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageBuilderComponentUserListRequest;
use App\Models\User;
use App\Services\CountryService;
use App\Services\GlobalOptionService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class ApiPageBuilderComponentUserListController extends Controller
{
    private $metaKeys = ['country'];

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

        $roles = $request->get('roles');
        $excludedId = $request->get('excluded_user');
        $defaultCountries = $request->get('default_countries');
        $defaultTypes = $request->get('default_types');
        $country = $request->get('country');
        $orderBy = $request->get('order_by');
        $type = $request->get('type');

        $users = User::available()
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
            ->get([
                'id',
                'unique_key',
                'first_name',
                'last_name',
                'profile_photo_media_id',
            ]);

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
                ->values(),
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
            $q->inRandomOrder();
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
}
