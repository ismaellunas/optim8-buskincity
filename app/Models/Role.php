<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    // Scopes:
    public function scopeWithoutSuperAdmin($query)
    {
        return $query->whereNotIn('name', [config('permission.super_admin_role')]);
    }

    public function scopeWithoutAdmin($query)
    {
        return $query->whereNotIn('name', [
            config('permission.super_admin_role'),
            config('permission.role_names.admin')
        ]);
    }

    public function saveFromInputs(array $inputs)
    {
        $this->name = $inputs['name'];
        $this->guard_name = 'web';
        $this->save();
    }

    public function getIsAdminRoleAttribute()
    {
        return $this->name == config('permission.role_names.admin');
    }
}
