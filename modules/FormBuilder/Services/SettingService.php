<?php

namespace Modules\FormBuilder\Services;

use Modules\FormBuilder\ModuleService;
use Modules\FormBuilder\Entities\FieldGroupSetting;

class SettingService
{
    public function getForm(string $fieldGroupId): array
    {
        $forms = [];

        $fieldGroupSettings = FieldGroupSetting::select([
                'key',
                'value',
            ])
            ->where('field_group_id', $fieldGroupId)
            ->get();

        if (!$fieldGroupSettings->isEmpty()) {
            foreach ($fieldGroupSettings as $setting) {
                $forms[$setting->key] = $setting->value;
            }

            return $forms;
        }

        return ModuleService::defaultSettings();
    }
}