<?php

namespace Modules\FormBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\FormBuilder\Services\FormBuilderService;

class PageBuilderController extends Controller
{
    private $formBuilderService;

    public function __construct(FormBuilderService $formBuilderService)
    {
        $this->formBuilderService = $formBuilderService;
    }

    public function formOptions()
    {
        return $this->formBuilderService->getFormOptions();
    }
}
