<?php

namespace Modules\FormBuilder\Forms\Locations;

use App\Entities\Forms\Locations\UserProfileLocation;
use App\Models\User;
use Illuminate\Support\Collection;

class GuestLocation extends UserProfileLocation
{
    /**
     * @override
     */
    public function canBeAccessedBy(?User $author = null): bool
    {
        return true;
    }

    /**
     * @override
     */
    public function canBeAccessedByEntity(array $locations = []): bool
    {
        return true;
    }

    /**
     * @override
     */
    public function getValues(Collection $keys): Collection
    {
        return collect([]);
    }
}