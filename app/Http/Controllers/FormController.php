<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormValueRequest;
use App\Models\Form;
use App\Models\FormValue;
use App\Services\FormService;
use App\Traits\FlashNotifiable;

class FormController extends Controller
{
    use FlashNotifiable;

    private $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    public function getSchema($formName)
    {
        $form = $this->formService->getFormByName($formName);

        $values = FormValue::where('form_id', $form->id)
            ->where('user_id', auth()->user()->id)
            ->value('data') ?? [];

        if ($form) {
            return $form->schema($values);
        }

        return [];
    }

    public function submit(FormValueRequest $request, $formName)
    {
        $inputs = $request->all();

        $formId = Form::where('name', $formName)->value('id');

        $formValue = FormValue::firstOrNew([
            'form_id' => $formId,
            'user_id' => auth()->user()->id,
        ]);

        $formValue->data = $inputs;

        $formValue->save();

        $this->generateFlashMessage('Saved');

        return back();
    }
}
