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
                'deleted_at',
            ]);
    }

    public function getRecords(
        User $user,
        string $term = null,
        int $perPage = 15,
        ?array $scopes = null
    ): LengthAwarePaginator {
        $records = $this->getBuilderRecords($term, $scopes);

        if (!$user->isSuperAdministrator) {
            $records = $records->whereDoesntHave('roles', function ($query) {
                    $query->where('name', config('permission.super_admin_role'));
                });
        }

        $records = $records->paginate($perPage);

        $this->transformRecords($records, $user);

        return $records;
    }

    public function getTrashedRecords(
        string $term = null,
        int $perPage = 15,
        ?array $scopes = null
    ): LengthAwarePaginator {
        return $this->getBuilderRecords($term, $scopes)
            ->onlyTrashed()
            ->paginate($perPage);
    }

    public function getLatestRegistrations(
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
            ->with(['profilePhoto' => function ($query) {
                $query->select([
                    'id',
                    'extension',
                    'file_name',
                    'file_url',
                    'version',
                ]);
            }])
            ->limit($limit)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', config('permission.super_admin_role'));
            })
            ->get()
            ->map(function ($user) {
                $props = [
                    'id',
                    'optimized_profile_photo_url',
                    'full_name',
                    'email',
                    'registered_at',
                ];

                $record = $user->only($props);

                return $record;
            })
            ->all();
    }

    public function transformRecords(AbstractPaginator $records, User $actor)
    {
        $records->getCollection()->transform(function ($user) use ($actor) {
            $user->can = [
                'delete_user' => $actor->can('delete', $user),
                'public_profile' => $user->hasPublicPage,
                'send_password_reset_email' => $actor->can('sendPasswordResetEmail', $user),
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

    public function deleteResources($userId): void
    {
        $pages = Page::where('author_id', $userId)->get();

        foreach ($pages as $page) {
            $page->delete();
        }

        $posts = Post::where('author_id', $userId)->get();

        foreach ($posts as $post) {
            $post->delete();
        }
    }

    public function passwordResetExpiryOptions(): Collection
    {
        return collect([
            [
                'id' => '1 hour',
                'value' => trans_choice(':number hour|:number hours', 1, ['number' => 1]),
            ], [
                'id' => '1 day',
                'value' => trans_choice(':number day|:number days', 1, ['number' => 1]),
            ], [
                'id' => '1 week',
                'value' => trans_choice(':number week|:number weeks', 1, ['number' => 1]),
            ], [
                'id' => '1 month',
                'value' => trans_choice(':number month|:number months', 1, ['number' => 1]),
            ]
        ]);
    }

    public static function resetPasswordEmailTags(User $user = null): array
    {
        return [
            'email' => $user->email ?? null,
            'first_name' => $user->first_name ?? null,
            'last_name' => $user->last_name ?? null,
            'app_name' => config('app.name'),
            'password_reset_button_link' => null,
            'expired_on' => null,
        ];
    }
}
