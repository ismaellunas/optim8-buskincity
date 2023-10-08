<?php

namespace App\Observers;

use App\Models\Role;
use App\Services\SettingService;

class RoleObserver
{
   public function deleted(Role $role)
    {
        $roleId = $role->id;
        $settingService = app(SettingService::class);

        $setting = collect(
                $settingService->getPublicPageProfileSlugCustomField()
            )
            ->whereNotIn('role_id', [$roleId])
            ->all();

        $settingService->saveKey(
            'public_page_profile_slug_custom_field',
            $setting,
        );
    }
}
