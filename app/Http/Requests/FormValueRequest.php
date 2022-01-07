<?php

namespace App\Http\Requests;

use App\Entities\Forms\Form;
use App\Models\User;
use App\Services\FormService;
use Illuminate\Foundation\Http\FormRequest;

class FormValueRequest extends FormRequest
{
    private $form;
    private $entity;

    protected $errorBag = 'formBuilder';

    public function authorize()
    {
        if (!$this->route('formName')) {
            return false;
        }

        $form = $this->getForm($this->route('formName'));

        $entity = null;

        if ($this->get('id')) {
            $entity = $this->getEntity($this->get('id'));
        }

        return $form->canBeAccessed($entity);
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

    private function getEntity($id): User
    {
        if ($this->entity === null) {
            $this->entity = User::find($id);
        }

        return $this->entity;
    }

    private function getForm(string $formName): ?Form
    {
        if ($this->form === null) {
            $formService = app(FormService::class);

            $this->form = $formService->getFormByName(
                $formName,
                auth()->user()
            );
        }

        return $this->form;
    }
}
