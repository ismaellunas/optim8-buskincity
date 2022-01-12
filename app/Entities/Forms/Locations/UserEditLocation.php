<?php

namespace App\Entities\Forms\Locations;

use App\Models\User;

class UserEditLocation extends UserProfileLocation
{
    public function canBeAccessedBy(?User $author = null): bool
    {
        return (
            parent::canBeAccessedBy($author)
            || $author->hasPermissionTo('user.edit')
        );
    }
}
