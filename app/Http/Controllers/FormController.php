<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormValueRequest;
use App\Models\{
    Form,
    FormValue,
    User,
};
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
        $form = $this->formService->getFormByName(
            $formName,
            auth()->user()
        );

        $entity = User::find(request()->get('id'));

        if (!$form || !$form->canBeAccessed($entity)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }

        $values = FormValue::where('form_id', $form->id)
            ->where('user_id', request()->get('id'))
            ->value('data') ?? [];

        if ($form) {
            return $form->schema($values);
        }

        return [];
    }

    public function submit(FormValueRequest $request, $formName)
    {
        $inputs = $request->validated();

        $formId = Form::where('name', $formName)->value('id');

        $formValue = FormValue::firstOrNew([
            'form_id' => $formId,
            'user_id' => $request->get('id'),
        ]);

        $formValue->data = $inputs;

        $formValue->save();

        $this->generateFlashMessage('Saved');

        return back();
    }
}
