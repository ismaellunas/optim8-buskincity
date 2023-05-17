<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Models\User;
use App\Traits\FlashNotifiable;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as IlluminateValidator;
use Modules\FormBuilder\Emails\AutomateUserCreationEmail;
use Modules\FormBuilder\Emails\AutomateUserUpdateEmail;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Entities\FormMappingRule;
use Modules\FormBuilder\Http\Requests\AutomateUserCreationRequest;
use Modules\FormBuilder\Services\AutomateUserCreationService;
use Modules\FormBuilder\Services\FormEntryService;
use Symfony\Component\HttpFoundation\Response;

class AutomateUserCreationController extends Controller
{
    use FlashNotifiable;

    public function __construct(
        private AutomateUserCreationService $automateUserCreationService,
        private FormEntryService $formEntryService
    ) {}

    private function getStoredMappingRule(Form $form, string $group)
    {
        return $form->userCreationMappingRules->where('group', $group);
    }

    private function createFormMappingRule(int $formId, string $group): FormMappingRule
    {
        $formMappingRule = new FormMappingRule();
        $formMappingRule->form_id = $formId;
        $formMappingRule->type = 'automate_user_creation';
        $formMappingRule->group = $group;

        return $formMappingRule;
    }

    private function saveUserRule(FormMappingRule $formMappingRule, string $column, ?array $input)
    {
        if (empty($input)) {
            $formMappingRule->from = null;
        } else {
            $formMappingRule->from = [
                'id' => Arr::get($input, 'id'),
                'name' => Arr::get($input, 'name'),
            ];
        }

        $formMappingRule->to = [
            'column' => $column,
            'table' => 'user',
        ];

        return $formMappingRule->save();
    }

    private function saveMandatoryFields(Form $form, array $inputs)
    {
        $formId = $form->id;

        $formMappingRules = $this->getStoredMappingRule($form, 'user');

        foreach ($inputs as $column => $mappingRule) {

            $formMappingRule = $formMappingRules->firstWhere('to.column', $column);

            if (!$formMappingRule) {
                $formMappingRule = $this->createFormMappingRule($formId, 'user');
            }

            $this->saveUserRule($formMappingRule, $column, $mappingRule);
        }
    }

    private function saveProfilePictureField(Form $form, ?array $input)
    {
        $formMappingRule = $this
            ->getStoredMappingRule($form, 'user')
            ->firstWhere('to.column', 'profile_photo_media_id');

        if (! $formMappingRule) {
            $formMappingRule = $this->createFormMappingRule($form->id, 'user');
        }

        $this->saveUserRule($formMappingRule, 'profile_photo_media_id', $input);
    }

    private function removeOptionalFields(
        array $mappingRules,
        Collection $storedMappingRules
    ) {
        $deleteFieldIds = $storedMappingRules
            ->map(fn ($field) => $field->id)
            ->diff(
                collect($mappingRules)
                    ->filter(fn ($field) => !Str::startsWith($field['id'], '_'))
                    ->map(fn ($field) => Arr::get($field, 'id'))
                    ->all()
            )->all();

        FormMappingRule::destroy($deleteFieldIds);
    }

    private function saveOptionalFields(
        int $formId,
        array $mappingRules,
        Collection $storedMappingRules
    ) {
        foreach ($mappingRules as $mappingRule) {
            $formMappingRule = null;

            if (! Str::startsWith($mappingRule['id'], '_')) {
                $formMappingRule = $storedMappingRules
                    ->firstWhere('id', $mappingRule['id']);
            }

            if (! $formMappingRule) {
                $formMappingRule = $this->createFormMappingRule($formId, 'form');
            }

            $formMappingRule->from = [
                'id' => Arr::get($mappingRule, 'from.id'),
                'name' => Arr::get($mappingRule, 'from.name'),
            ];

            $formMappingRule->to = [
                'form_id' => Arr::get($mappingRule, 'to.form_id'),
                'name' => Arr::get($mappingRule, 'to.name'),
            ];

            $formMappingRule->save();
        }
    }

    private function saveRole(Form $form, ?int $role)
    {
        $formId = $form->id;

        $formMappingRule = $this->getStoredMappingRule($form, 'role')->first();

        if (! $formMappingRule) {
            $formMappingRule = $this->createFormMappingRule($formId, 'role');
        }

        $formMappingRule->to = ['role' => $role];

        $formMappingRule->save();
    }

    private function saveCreateUserEmail(Form &$form, ?string $value)
    {
        $setting = $form->setting;

        Arr::set($setting, 'email.automate_user_creation', $value);

        $form->setting = $setting;
    }

    private function saveUpdateUserEmail(Form &$form, ?string $value)
    {
        $setting = $form->setting;

        Arr::set($setting, 'email.automate_user_update', $value);

        $form->setting = $setting;
    }

    public function save(AutomateUserCreationRequest $request, Form $formBuilder)
    {
        $this->saveMandatoryFields(
            $formBuilder,
            $request->only('email', 'first_name', 'last_name')
        );

        $this->saveProfilePictureField(
            $formBuilder,
            $request->get('profile_picture')
        );

        $this->saveRole($formBuilder, $request->get('role'));

        $storedMappingRules = $this->getStoredMappingRule($formBuilder, 'form');

        $mappingRule = $request->get('mapping_rules', []);

        $this->saveOptionalFields($formBuilder->id, $mappingRule, $storedMappingRules);

        $this->removeOptionalFields($mappingRule, $storedMappingRules);

        $this->saveCreateUserEmail($formBuilder, $request->get('create_user_email'));

        $this->saveUpdateUserEmail($formBuilder, $request->get('update_user_email'));

        $formBuilder->save();

        $this->generateFlashMessage('Saved');
    }

    private function validateFomEntry(Form $formBuilder, FormEntry $formEntry)
    {
        return Validator::make(
            $this->automateUserCreationService->getUserProperties(
                $formEntry,
                $formBuilder->mappingUserRules,
            ),
            [
                'email' => [
                    'required',
                    'email',
                    function ($attribute, $value, $fail) {
                        $roles = explode('|', config('permission.admin_or_super_admin'));

                        $user = User::email($value)->inRoleNames($roles)->first();

                        if ($user) {
                            $fail(__('validation.email_belongs_to_protected_user'));
                        }
                    },
                ],
                'first_name' => [ 'required', ],
                'last_name' => [ 'required', ],
            ],
            [],
            $this->automateUserCreationService->getMandatoryLabels($formBuilder)
        );
    }

    private function mandatoryFieldErrorResponse(?IlluminateValidator $validator = null)
    {
        $errorMessage = __('Mandatory fields are required to create/update the user.');

        $this->generateFlashMessage($errorMessage);

        return back()->withErrors($validator ?? [$errorMessage]);
    }

    public function createOrUpdateUser(Form $formBuilder, FormEntry $formEntry)
    {
        $validator = $this->validateFomEntry($formBuilder, $formEntry);

        if ($validator->fails()) {
            return $this->mandatoryFieldErrorResponse($validator);
        }

        DB::beginTransaction();

        try {
            $user = $this->automateUserCreationService->createOrUpdate(
                $formBuilder,
                $formEntry
            );

            if ($user->wasRecentlyCreated) {

                Mail::to($user)->queue(
                    new AutomateUserCreationEmail($user, $formBuilder)
                );

                $this->generateFlashMessage("The :resource was created!", [
                    'resource' => __('User'),
                ]);

            } else {

                Mail::to($user)->queue(
                    new AutomateUserUpdateEmail($user, $formBuilder)
                );

                $this->generateFlashMessage("The :resource was updated!", [
                    'resource' => __('User'),
                ]);
            }

            $this->automateUserCreationService->markAutomateActionIsDone($formEntry);

            if (! $formEntry->read_at) {
                $this->formEntryService->markAsRead([$formEntry->id]);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->getCode() == '23502') {
                return $this->mandatoryFieldErrorResponse();
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function confirmation(FormEntry $formEntry)
    {
        $validator = $this->validateFomEntry($formEntry->form, $formEntry);

        if ($validator->fails()) {
            return response()->json(
                $validator->errors(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $userRules = $formEntry
            ->form
            ->userCreationMappingRules
            ->where('group', 'user');

        $userProps = $this
            ->automateUserCreationService
            ->getUserProperties($formEntry, $userRules);

        $user = User::firstWhere('email', $userProps['email']);

        $message = null;
        $isExists = false;

        if ($user) {

            $message = __("User with email :email already exists. Proceeding will update their details.", [
                'email' => $user->email,
            ]);

            $isExists = true;

        } else {

            $message = __("A new user named ':name' with the email address ':email' will be created.", [
                'email' => $userProps['email'],
                'name' => $userProps['first_name'] . ' ' . $userProps['last_name'],
            ]);
        }

        return response()->json([
            'message' => $message,
            'isExists' => $isExists,
        ]);
    }
}
