<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Http\Requests\SettingRequest;

class SettingController extends CrudController
{
    public function update(SettingRequest $request, Form $formBuilder)
    {
        $inputs = $request->validated();

        $formBuilder->saveSettingFromInputs($inputs);

        $this->generateFlashMessage('Setting updated successfully!');

        return redirect()->back();
    }
}
