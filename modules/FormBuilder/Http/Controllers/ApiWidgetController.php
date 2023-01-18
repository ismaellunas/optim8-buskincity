<?php

namespace Modules\FormBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Services\FormBuilderService;

class ApiWidgetController extends Controller
{
    private $formBuilderService;

    public function __construct(FormBuilderService $formBuilderService)
    {
        $this->formBuilderService = $formBuilderService;
    }

    public function getEntries(FieldGroup $formBuilder)
    {
        $fieldLabels = collect(
                $this->formBuilderService->getFieldLabels(
                    $formBuilder->data['fields'],
                )
            )
            ->slice(0, 3)
            ->all();

        $fieldNames = collect(
                $this->formBuilderService->getDataFromFields(
                    $formBuilder->data['fields'],
                    'name'
                )
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
