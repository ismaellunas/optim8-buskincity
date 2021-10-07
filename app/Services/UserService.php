<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private function getBuilderRecords(
        string $term = null
    ): Builder {
        return User::orderBy('id', 'DESC')
            ->with([
                'roles' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->select([
                'id',
                'name',
                'email',
            ]);
    }

    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this
            ->getBuilderRecords($term)
            ->paginate($perPage);
    }

    public function getNoSuperAdministratorRecords(
        string $term = null,
        int $perPage = 15
    ) {
        return $this
            ->getBuilderRecords($term)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', config('permission.super_admin_role'));
            })
            ->paginate($perPage);
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
}
