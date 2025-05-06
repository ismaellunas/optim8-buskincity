<?php

namespace App\Http\Requests;

use App\Services\FormService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FormValueRequest extends BaseFormRequest
{
    protected $forms;
    protected $formLocation;

    protected $errorBag = 'formBuilder';

    public function authorize()
    {
        $routeName = $this->get('route_name');

        $entityId = $this->get('id');

        if (empty($routeName)) {
            return false;
        }

        $formService = app(FormService::class);

        $formLocation = $formService->getFormLocation(
            $routeName,
            $entityId
        );

        return $formLocation->canBeAccessedBy(Auth::user());
    }

    public function rules()
    {
        $formService = app(FormService::class);

        $forms = $this->getForms();

        $location = $this->getFormLocation();

        $rules = $formService->getRules($forms, $location);

        return $rules;
    }

    protected function customAttributes(): array
    {
        $formService = app(FormService::class);

        $attributes = $formService->getAttributes($this->getForms(), $this->all());

        return $attributes;
    }

    protected function getForms(): Collection
    {
        $formService = app(FormService::class);

        if (is_null($this->forms)) {
            $this->forms = $formService->getFormsOnKeyAndRoute(
                $this->get('key'),
                $this->get('route_name'),
                Auth::user()
            );
        }

        return $this->forms;
    }

    protected function getFormLocation()
    {
        $formService = app(FormService::class);

        if (is_null($this->formLocation)) {
            $this->formLocation = $formService->getFormLocation(
                $this->get('route_name'),
                $this->get('id')
            );
        }

        return $this->formLocation;
    }
}
