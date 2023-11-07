<?php

namespace Modules\Booking\Policies;

use App\Models\User;
use Modules\Space\Entities\Space;
use Modules\Space\ModuleService;

class SpacePolicyMixin
{
    public function bookAProduct()
    {
        return function (User $user, Space $space) {
            if (
                ! $space->product
                || ! $space->product->isPublished
                || ! app(ModuleService::class)->isModuleActive()
            ) {
                return false;
            }

            return $user->hasRole($space->product->roles);
        };
    }

    public function manageProductSpace()
    {
        return function (User $user) {
            if (
                $user->can('viewAny', Space::class)
                && app(ModuleService::class)->isModuleActive()
            ) {
                return true;
            }

            return false;
        };
    }
}