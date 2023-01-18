<?php

namespace App\Services;

use App\Models\{
    Page,
    Post,
    Role,
    User,
};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private function getBuilderRecords(
        string $term = null,
        ?array $scopes = null
    ): Builder {
        return User::orderBy('id', 'DESC')
            ->with([
                'roles' => function ($query) {
                    $query->select('id', 'name', 'guard_name');
                    $query->with([
                        'permissions' => function ($query) {
                            $query->select('id', 'name', 'guard_name');
                        },
                    ]);
                }
            ])
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->when($scopes, function ($query) use ($scopes) {
                foreach ($scopes as $scopeName => $value) {
                    if (!is_null($value)) {
                        $query->$scopeName($value);
                    }
                }
            })
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'is_suspended',
                'unique_key',
            ]);
    }

    public function getRecords(
        string $term = null,
        int $perPage = 15,
        ?array $scopes = null
    ): LengthAwarePaginator {
        return $this
            ->getBuilderRecords($term, $scopes)
            ->paginate($perPage);
    }

    public function getLatestRegistrations(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $limit = 10,
    ): array {
        return User::latest()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'is_suspended',
                'profile_photo_media_id',
                'created_at',
            ])
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->when($scopes, function ($query) use ($scopes) {
                foreach ($scopes as $scopeName => $value) {
                    if (!is_null($value)) {
                        $query->$scopeName($value);
                    }
                }
            })
            ->limit($limit)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', config('permission.super_admin_role'));
            })
            ->get()
            ->append('registered_at')
            ->toArray();
    }

    public function getNoSuperAdministratorRecords(
        string $term = null,
        int $perPage = 15,
        ?array $scopes = null
    ): LengthAwarePaginator {
        return $this
            ->getBuilderRecords($term, $scopes)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', config('permission.super_admin_role'));
            })
            ->paginate($perPage);
    }

    public function transformRecords(AbstractPaginator $records, User $actor)
    {
        $records->getCollection()->transform(function ($user) use ($actor) {
            $user->can = [
                'delete_user' => $actor->can('delete', $user),
                'public_profile' => $user->hasPublicPage,
            ];

            $user->append('profile_page_url');

            return $user;
        });
    }

    public function getRoleOptions(): array
    {
        return Role::withoutSuperAdmin()
            ->get(['id', 'name'])
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'value' => $role->name,
                ];
            })
            ->all();
    }

    public static function hashPassword($password): string
    {
        return Hash::make($password);
    }

    public static function splitName(string $name): array
    {
        $name = explode(' ', $name);
        $firstName = $name[0];
        $lastName = $name[count($name) - 1];
        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
        ];
    }

    public function reassignResources($userIdFrom, $userIdTo)
    {
        Post::where('author_id', $userIdFrom)->update(['author_id' => $userIdTo]);
        Page::where('author_id', $userIdFrom)->update(['author_id' => $userIdTo]);
    }
}
