<?php

namespace Modules\Booking\Services;

use App\Services\SettingService as AppSettingService;

class SettingService extends AppSettingService
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