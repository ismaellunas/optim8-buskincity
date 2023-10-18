<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use Modules\Space\Entities\Space;

class SpacePolicyMixin
{
    public function bookAProduct()
    {
        return function (User $user, Space $space) {
            if (! $space->product) {
                return false;
            }

            return $user->hasRole($space->product->roles);
        };
    }
}