<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\FormEntry;
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
        $this->authorizeResource(Form::class, 'form_builder');

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
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getCreateTitle(),
                ],
            ],
            'title' => $this->getCreateTitle(),
        ]));
    }

    public function store(FormBuilderRequest $request)
    {
        $inputs = $request->validated();

        $form = new Form();
        $form->saveFromInputs($inputs);

        if (!empty($inputs['field_groups'])) {
            $fieldGroup = new FieldGroup();

            $fieldGroup->syncFieldGroups($inputs['field_groups'], $form->id);
        }

        $this->generateFlashMessage('Form created successfully!');

        return redirect()->route($this->baseRouteName . '.edit', $form->id);
    }

    public function edit(Form $formBuilder)
    {
        $formBuilder->load('fieldGroups');
        $formBuilder = $formBuilder->toArray();

        $formBuilder['form_id'] = $formBuilder['key'];

        unset($formBuilder['key']);

        return Inertia::render('FormBuilder::Edit', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $this->getEditTitle(),
                ],
            ],
            'baseRouteNameSetting' => $this->baseRouteNameSetting,
            'formBuilder' => $formBuilder,
            'title' => __('Editing :name Form', [
                'name' => $formBuilder['name']
            ]),
        ]));
    }

    public function update(FormBuilderRequest $request, Form $formBuilder)
    {
        $inputs = $request->validated();

        $formBuilder->saveFromInputs($inputs);

        if (!empty($inputs['field_groups'])) {
            $fieldGroup = new FieldGroup();

            $fieldGroup->syncFieldGroups($inputs['field_groups'], $formBuilder->id);
        }

        $this->generateFlashMessage('Form updated successfully!');

        return redirect()->route($this->baseRouteName . '.edit', $formBuilder->id);
    }

    public function destroy(Form $formBuilder)
    {
        $formBuilder->delete();

        $this->generateFlashMessage('Form deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
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

        $formEntry = $this->formBuilderService->saveValues($inputs);

        FormSubmitted::dispatch($formEntry);

        return [
            'success' => true,
            'message' => __('Thank you for filling out the form.'),
        ];
    }
}
