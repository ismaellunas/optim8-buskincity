<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Modules\FormBuilder\Entities\FieldGroup;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Events\FormSubmitted;
use Modules\FormBuilder\Http\Requests\FormBuilderFrontendRequest;
use Modules\FormBuilder\Http\Requests\FormBuilderRequest;
use Modules\FormBuilder\Services\AutomateUserCreationService;
use Modules\FormBuilder\Services\FormBuilderService;

class FormBuilderController extends CrudController
{
    protected $baseRouteName = 'admin.form-builders';
    protected $baseRouteNameSetting = 'admin.form-builders.settings';
    protected $recordsPerPage = 10;
    protected $title = 'Form Builder';

    public function __construct(
        private FormBuilderService $formBuilderService,
        private AutomateUserCreationService $automateUserCreationService,
        private UserService $userService
    ) {
        $this->authorizeResource(Form::class, 'form_builder');
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
            'i18n' => [
                'search' => __('search'),
                'create_new' => __('Create new'),
                'name' => __('Name'),
                'form_id' => __('Form ID'),
                'entries' => __('Entries'),
                'actions' => __('Actions'),
                'are_you_sure' => __('Are you sure?'),
            ],
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
            'i18n' => $this->translationCreateEditPage(),
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

        $this->generateFlashMessage('The :resource was created!', [
            'resource' => __('Form')
        ]);

        return redirect()->route($this->baseRouteName . '.edit', $form->id);
    }

    public function edit(Form $formBuilder)
    {
        $formBuilder->load('fieldGroups');
        $formBuilderArray = $formBuilder->toArray();

        $formBuilderArray['form_id'] = $formBuilderArray['key'];

        unset($formBuilderArray['key']);

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
            'formBuilder' => $formBuilderArray,
            'title' => __('Editing :name Form', [
                'name' => $formBuilderArray['name']
            ]),
            'fields' => $this->automateUserCreationService->getFields($formBuilder),
            'roleOptions' => $this->automateUserCreationService->getRoleOptions(),
            'i18n' => $this->translationCreateEditPage(),
            'userFields' => $this->automateUserCreationService->getUserFields(),
            'mappingRules' => $this->automateUserCreationService->getMappingRules($formBuilder),
            'matchedTypes' => $this->automateUserCreationService->matchedTypes(),
            'mandatoryMatchedTypes' => $this->automateUserCreationService->mandatoryMatchedTypes(),
            'emailTags' => array_keys($this->automateUserCreationService->emailTags()),
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

        $this->automateUserCreationService->removeUntrackedRules($formBuilder);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Form')
        ]);

        return redirect()->route($this->baseRouteName . '.edit', $formBuilder->id);
    }

    public function destroy(Form $formBuilder)
    {
        $formBuilder->delete();

        $this->generateFlashMessage('The :resource was deleted!', [
            'resource' => __('Form')
        ]);

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

    private function translationCreateEditPage(): array
    {
        return [
            'builder' => __('Builder'),
            'notifications' => __('Notifications'),
            'settings' => __('Settings'),
            'name' => __('Name'),
            'form_id' => __('Form ID'),
            'general' => __('General'),
            'add_field_group' => __('Add :resource', ['resource' => __('Field group')]),
            'cancel' => __('Cancel'),
            'create' => __('Create'),
            'update' => __('Update'),
            'search' => __('Search'),
            'create_new' => __('Create new'),
            'subject' => __('Subject'),
            'status' => __('Status'),
            'actions' => __('Actions'),
            'are_you_sure' => __('Are you sure?'),
            'submit_button' => __('Submit button'),
            'text' => __('Text'),
            'position' => __('Position'),
            'update' => __('Update'),
            'add' => __('Add'),
            'automate_user_creation' => __('Automate user creation'),
            'change_role_confirmation_text' => __('All mapping rules will be removed. Are you sure you want to change the role?'),
            'change_role_confirmation_title' => __('Side effect of changing this role'),
            'continue' => __('Continue'),
            'email' => __('Email'),
            'email_templates' => __('Email templates'),
            'first_name' => __('First name'),
            'form_field' => __('Form field'),
            'last_name' => __('Last name'),
            'map_form_field_to_user_field' => __('Map Form Field to User field'),
            'mapping_rules' => __('Mapping rules'),
            'none' => __('None'),
            'profile_picture' => __('Profile picture'),
            'role' => __('Role'),
            'role_that_will_be_assigned' => __('Role that will be assigned'),
            'user_creation' => __('User creation'),
            'user_field' => __('User field'),
            'user_properties' => __('User properties'),
            'user_update' => __('User update'),
            'yes' => __('Yes'),
        ];
    }
}
