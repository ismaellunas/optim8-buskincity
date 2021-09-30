<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Scopes:
    public function scopeWithoutSuperAdmin($query)
    {
        return $query->whereNotIn('name', [config('permission.super_admin_name')]);
    }

    public function saveFromInputs(array $inputs)
    {
        $this->name = $inputs['name'];
        $this->save();
    }
}
