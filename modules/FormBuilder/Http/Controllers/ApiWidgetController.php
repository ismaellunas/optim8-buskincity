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
        $allFields = $this->formBuilderService
            ->getAllFields($formBuilder->fieldGroups);

        $fieldLabels = collect(
                $this->formBuilderService->getFieldLabels(
                    $allFields,
                )
            )
            ->slice(0, 3)
            ->all();

        $fieldNames = collect(
                $this->formBuilderService->getDataFromFields(
                    $allFields,
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
