<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function getRecords(): LengthAwarePaginator
    {
        return User::orderBy('id', 'DESC')
            ->select([
                'id',
                'name',
                'email',
            ])
            ->paginate();
    }

    public function getRoleOptions(): array
    {
        return Role::whereNotIn('name', [config('permission.super_admin_name')])
            ->get(['id', 'name'])
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'value' => $role->name,
                ];
            })
            ->all();
    }

    public function hashPassword($password): string
    {
        return Hash::make($password);
    }
}
