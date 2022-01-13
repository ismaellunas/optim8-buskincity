<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormValueRequest;
use App\Services\FormService;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    use FlashNotifiable;

    private $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    public function getSchemas(Request $request)
    {
        $schemas = $this->formService->getSchemas(
            $request->get('route_name'),
            Auth::user(),
            $request->get('id')
        );

        return $schemas->sortBy('order')->all();
    }

    public function submit(FormValueRequest $request)
    {
        $this->formService->saveValues(
            $request->validated(),
            $request->get('route_name'),
            Auth::user(),
            $request->get('id')
        );

        $this->generateFlashMessage('Saved');

        return back();
    }
}
