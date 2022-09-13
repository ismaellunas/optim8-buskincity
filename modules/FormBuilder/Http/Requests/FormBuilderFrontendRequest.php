<?php

namespace Modules\FormBuilder\Http\Requests;

use App\Http\Requests\FormValueRequest;
use Illuminate\Support\Collection;
use Modules\FormBuilder\Services\FormBuilderService;

class FormBuilderFrontendRequest extends FormValueRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['form_id'] = [
            'required',
            'exists:Modules\FormBuilder\Entities\FieldGroup,title'
        ];

        return $rules;
    }

    protected function getForms(): Collection
    {
        $formBuilderService = app(FormBuilderService::class);

        if (is_null($this->forms)) {
            $this->forms = collect([
                $formBuilderService->getForm(
                    $this->get('form_id'),
                )
            ]);
        }

        return $this->forms;
    }

    protected function getFormLocation()
    {
        $formBuilderService = app(FormBuilderService::class);

        if (is_null($this->formLocation)) {
            $this->formLocation = $formBuilderService->getFormLocation();
        }

        return $this->formLocation;
    }
}
