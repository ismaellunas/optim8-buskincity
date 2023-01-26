<?php

namespace Modules\FormBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Services\FormBuilderService;

class ApiWidgetController extends Controller
{
    private $formBuilderService;

    public function __construct(FormBuilderService $formBuilderService)
    {
        $this->formBuilderService = $formBuilderService;
    }

    public function getEntries(Form $formBuilder)
    {
        $fieldLabels = collect(
                $formBuilder->getFieldLabels()
            )
            ->slice(0, 3)
            ->all();

        $fieldNames = collect(
                $formBuilder->getFieldNames()
            )
            ->slice(0, 3)
            ->all();

        return [
            'fieldLabels' => $fieldLabels,
            'fieldNames' => $fieldNames,
            'records' => $this->formBuilderService->getWidgetEntryRecords(
                $formBuilder
            ),
        ];
    }
}
