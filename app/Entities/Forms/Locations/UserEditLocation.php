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

    public function canBeAccessedByEntity(array $options = []): bool
    {
        if (is_null($this->entity)) {
            return false;
        }

        foreach ($options['locations'] as $location) {
            if (
                $location['name'] == 'admin.profile.show'
                && !empty($location['visibility']['roles'])
            ) {
                return $this->entity->hasRole($location['visibility']);
            }
        }

        return false;
    }
}
