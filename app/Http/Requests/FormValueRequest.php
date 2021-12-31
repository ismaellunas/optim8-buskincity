<?php

namespace App\Http\Requests;

use App\Entities\Forms\Form;
use App\Services\FormService;
use Illuminate\Foundation\Http\FormRequest;

class FormValueRequest extends FormRequest
{
    private $form;

    protected $errorBag = 'formBuilder';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [];

        $form = $this->getForm($this->route('formName'));

        if (!empty($form)) {
            $rules = $form->rules();
        }

        return $rules;
    }

    public function attributes()
    {
        $form = $this->getForm($this->route('formName'));

        if (!empty($form)) {
            return $form->attributes();
        }

        return [];
    }

    private function getForm(string $formName): ?Form
    {
        if ($this->form === null) {
            $formService = app(FormService::class);

            $this->form = $formService->getFormByName($formName);
        }

        return $this->form;
    }
}
