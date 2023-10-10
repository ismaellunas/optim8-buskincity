<?php

namespace Modules\Booking\Policies;

use App\Models\User;
use Modules\Booking\Services\SettingService;

class ProductPolicyMixin
{
    public function showFrontendProduct()
    {
        return function(User $user) {
            $canCommonUserAccessed = app(SettingService::class)->getAccessCommonUser();
            $accessRoleIds = app(SettingService::class)->getAccessRoleIds();


            return (
                $user->hasRole($accessRoleIds)
                || (
                    $user->roles->isEmpty()
                    && $canCommonUserAccessed
                )
            );
        };
    }
}
