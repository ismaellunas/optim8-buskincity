<?php

namespace Modules\FormBuilder\Forms\Locations;

use App\Models\User;
use App\Entities\Forms\Locations\UserProfileLocation;

class GuestLocation extends UserProfileLocation
{
    /**
     * @override
     */
    public function canBeAccessedBy(?User $author = null): bool
    {
        return true;
    }
}