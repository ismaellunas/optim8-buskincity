<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return User::orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->select([
                'id',
                'name',
                'email',
            ])
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
