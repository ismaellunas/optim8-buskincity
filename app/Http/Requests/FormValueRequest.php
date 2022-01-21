<?php

namespace App\Http\Requests;

use App\Services\FormService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FormValueRequest extends FormRequest
{
    private $forms;

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

        $rules = $formService->getRules($forms);

        return $rules;
    }

    public function attributes()
    {
        $formService = app(FormService::class);

        $attributes = $formService->getAttributes($this->getForms());

        return $attributes;
    }

    private function getForms(): Collection
    {
        $formService = app(FormService::class);

        if (is_null($this->forms)) {
            $this->forms = $formService->getFormsOnRoute(
                $this->get('route_name'),
                Auth::user()
            );
        }

        return $this->forms;
    }
}
