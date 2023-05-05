<?php

namespace Modules\FormBuilder\Observers;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Modules\FormBuilder\Entities\Form;

class FormObserver
{
    public function creating(Form $form)
    {
        $this->addEmailSetting($form);
    }

    private function addEmailSetting(Form $form)
    {
        $setting = $form->setting ?? [];

        $emailSetting = Arr::get($setting, 'email', []);

        $settingKeys = [
            'automate_user_creation' => 'automate_user_creation_email',
            'automate_user_update' => 'automate_user_update_email',
        ];

        $formBuilderSetting = Setting::group('form_builder.email')
            ->whereIn('key', array_values($settingKeys))
            ->get(['key', 'value', 'group'])
            ->keyBy('key');

        foreach ($settingKeys as $key => $settingKey) {
            Arr::set(
                $emailSetting,
                $key,
                $formBuilderSetting->get($settingKey)->value ?? null
            );
        }

        $setting['email'] = $emailSetting;

        $form->setting = $setting;
    }
}
