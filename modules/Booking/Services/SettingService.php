<?php

namespace Modules\Booking\Services;

use App\Services\BaseSettingService;

class SettingService extends BaseSettingService
{
    public function getAccessCommonUser(): bool
    {
        $value = $this->getKey('booking_access_common_user');

        return (bool)$value;
    }

    public function getAccessRoleIds(): array
    {
        return $this->getArrayValueByKey('booking_access_roles');
    }
}