<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FieldGroupEntry;
use Modules\FormBuilder\Events\FormSubmitted;
use Modules\FormBuilder\Http\Requests\FormBuilderFrontendRequest;
use Modules\FormBuilder\Http\Requests\FormBuilderRequest;
use Modules\FormBuilder\Services\FormBuilderService;

class FormBuilderController extends CrudController
{
    protected $baseRouteName = 'admin.form-builders';
    protected $baseRouteNameSetting = 'admin.form-builders.settings';
    protected $recordsPerPage = 10;
    protected $title = 'Form Builder';

    private $formBuilderService;

    public function __construct(FormBuilderService $formBuilderService)
    {
        $this->authorizeResource(FieldGroup::class, 'form_builder');

        $this->formBuilderService = $formBuilderService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('FormBuilder::Index', $this->getData([
            'can' => [
                'browse' => $user->can('form_builder.browse'),
                'add' => $user->can('form_builder.add'),
                'edit' => $user->can('form_builder.edit'),
                'delete' => $user->can('form_builder.delete'),
            ],
            'records' => $this->formBuilderService->getRecords(
                $request->term,
                $this->recordsPerPage,
            ),
        ]));
    }

    public function create()
    {
        return Inertia::render('FormBuilder::Create', $this->getData([
            'title' => $this->getCreateTitle(),
        ]));
    }

    public function store(FormBuilderRequest $request)
    {
        $inputs = $request->validated();
        $fieldGroup = new FieldGroup();

        $fieldGroup->saveFromInputs($inputs);

        $this->generateFlashMessage('Form created successfully!');

        return redirect()->route($this->baseRouteName . '.edit', $fieldGroup->id);
    }

    public function edit(FieldGroup $formBuilder)
    {
        $formBuilder->builders = $formBuilder->data;
        $formBuilder->form_id = $formBuilder->title;
        unset($formBuilder->data);
        unset($formBuilder->title);

        return Inertia::render('FormBuilder::Edit', $this->getData([
            'baseRouteNameSetting' => $this->baseRouteNameSetting,
            'formBuilder' => $formBuilder,
            'title' => __('Editing :name Form', [
                'name' => $formBuilder->name
            ]),
        ]));
    }

    public function update(FormBuilderRequest $request, FieldGroup $formBuilder)
    {
        $inputs = $request->validated();

        $formBuilder->saveFromInputs($inputs);

        $this->generateFlashMessage('Form updated successfully!');

        return redirect()->route($this->baseRouteName . '.edit', $formBuilder->id);
    }

    public function destroy(FieldGroup $formBuilder)
    {
        $formBuilder->delete();

        $this->generateFlashMessage('Form deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function entries(Request $request, FieldGroup $formBuilder)
    {
        return Inertia::render('FormBuilder::Entries', $this->getData([
            'title' => $this->title . ' Entries - ' . $formBuilder->name,
            'formBuilder' => $formBuilder,
            'records' => $this->formBuilderService->getEntryRecords(
                $formBuilder,
                $request->term,
                $this->recordsPerPage,
            ),
            'fieldLabels' => $this->formBuilderService->getFieldLabels(
                $formBuilder->data['fields']
            ),
            'fieldNames' => $this->formBuilderService->getDataFromFields(
                $formBuilder->data['fields'],
                'name'
            ),
        ]));
    }

    public function entryShow(FieldGroup $formBuilder, FieldGroupEntry $entry)
    {
        $user = auth()->user();

        return Inertia::render('FormBuilder::EntryDetail', $this->getData([
            'title' => $this->title . ' Entry - ' . $formBuilder->name . ' # ' . $entry->id,
            'formBuilder' => $formBuilder,
            'entry' => $this->formBuilderService->transformEntry($entry),
            'entryDisplay' => $this->formBuilderService->getDisplayValues(
                $formBuilder->data['fields'],
                $entry,
            ),
            'fieldLabels' => $this->formBuilderService->getFieldLabelAndNames(
                $entry->fieldGroup->data['fields']
            ),
            'can' => [
                'user' => [
                    'edit' => $user->can('user.edit'),
                ]
            ]
        ]));
    }

    public function getSchema(Request $request)
    {
        $form = $this->formBuilderService->getForm($request->form_id);

        Gate::allowIf(
            !empty($form)
            && $form->canBeAccessed()
            && $this->formBuilderService->getFormLocation()->canBeAccessedBy()
        );

        return $form->schema();
    }

    public function submit(FormBuilderFrontendRequest $request)
    {
        $inputs = $request->validated();

        $fieldGroupEntry = $this->formBuilderService->saveValues($inputs);

        FormSubmitted::dispatch($fieldGroupEntry);

        return [
            'success' => true,
            'message' => __('Thank you for filling out the form.'),
        ];
    }
}
