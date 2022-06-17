<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\PageBuilderComponentUserListRequest;

class ApiPageBuilderComponentUserListController extends Controller
{
    public function __invoke(PageBuilderComponentUserListRequest $request)
    {
        $metaKeys = array_merge(
            ['country'],
            [
                'discipline',
                'stage_name',
            ]
        );

        $roles = $request->get('roles');
        $excludedId = $request->get('excluded_user');
        $countries = $request->get('countries');
        $orderBy = $request->get('order_by');

        $users = User::when($roles, function ($q, $roles) {
                $this->inRoles($q, $roles);
            })
            ->when($excludedId, function ($q, $excludedId) {
                $this->excludeUser($q, $excludedId);
            })
            ->when($countries, function ($q, $countries) {
                $this->inCountries($q, $countries);
            })
            ->when($orderBy, function($q, $orderBy) {
                $this->orderBy($q, $orderBy);
            })
            ->hasPermissionNames(['public_page.profile'])
            ->with([
                'metas' => function ($q) use ($metaKeys) {
                    $q->whereIn('key', $metaKeys);
                },
                'profilePhoto' => function ($q) {
                    $q->select([
                        'id',
                        'file_url',
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

        return $users->map(function ($user) use ($metaKeys) {

            $metas = collect($metaKeys)->mapWithKeys(function ($metaKey) use ($user) {
                $value = $user->metas->pluck('value', 'key')->get($metaKey);
                return [ $metaKey => $value ];
            })->all();

            return array_merge(
                [
                    'full_name' => $user->fullName,
                    'profile_photo_url' => (
                        $user->profilePhotoUrl
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

    private function inCountries($q, array $countries)
    {
        $q->whereHas('metas', function ($q) use ($countries) {
            $q->where('key', 'country');
            $q->whereIn('value', $countries);
        });
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
}
