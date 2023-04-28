<?php

namespace App\Entities\Forms\Locations;

use App\Models\User;

class UserEditLocation extends UserProfileLocation
{
    protected $location = 'admin.profile.show';

    protected function getEntity()
    {
        if (is_null($this->entity)) {
            $this->entity = User::withTrashed()->find($this->entityId);
        }

        return $this->entity;
    }

    public function canBeAccessedBy(?User $author = null): bool
    {
        return (
            parent::canBeAccessedBy($author)
            || ($author ? $author->hasPermissionTo('user.edit') : false)
        );
    }
}
