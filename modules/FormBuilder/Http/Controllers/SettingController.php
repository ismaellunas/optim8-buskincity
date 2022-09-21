<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupSetting;
use Modules\FormBuilder\Http\Requests\SettingRequest;
use Modules\FormBuilder\Services\SettingService;

class SettingController extends CrudController
{
    public function __construct(
        SettingService $settingService
    ) {
        $this->settingService = $settingService;
    }

    public function form(FieldGroup $formBuilder)
    {
        return $this->settingService->getForm(
            $formBuilder->id,
        );
    }

    public function update(SettingRequest $request, FieldGroup $formBuilder)
    {
        $inputs = $request->validated();

        foreach ($inputs as $key => $value) {
            FieldGroupSetting::updateOrCreate(
                [
                    'key' => $key,
                    'field_group_id' => $formBuilder->id
                ],
                [
                    'value' => $value
                ]
            );
        }

        $this->generateFlashMessage('Setting updated successfully!');

        return redirect()->back();
    }
}
