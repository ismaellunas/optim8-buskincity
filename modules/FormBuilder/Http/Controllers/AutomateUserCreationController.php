<?php

namespace Modules\FormBuilder\Http\Controllers;

use App\Traits\FlashNotifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Entities\FormMappingRule;
use Modules\FormBuilder\Http\Requests\AutomateUserCreationRequest;
use Modules\FormBuilder\Services\AutomateUserCreationService;

class AutomateUserCreationController extends Controller
{
    use FlashNotifiable;

    public function __construct(
        private AutomateUserCreationService $automateUserCreationService
    ) {}

    private function getStoredMappingRule(int $formId, string $group)
    {
        return FormMappingRule::where('form_id', $formId)
            ->where('type', 'automate_user_creation')
            ->where('group', $group)
            ->get();
    }

    private function createFormMappingRule(int $formId, string $group): FormMappingRule
    {
        $formMappingRule = new FormMappingRule();
        $formMappingRule->form_id = $formId;
        $formMappingRule->type = 'automate_user_creation';
        $formMappingRule->group = $group;

        return $formMappingRule;
    }

    private function saveMandatoryFields(int $formId, array $inputs)
    {
        $formMappingRules = $this->getStoredMappingRule($formId, 'user');

        foreach ($inputs as $column => $mappingRule) {

            $formMappingRule = $formMappingRules->firstWhere('to.column', $column);

            if (!$formMappingRule) {
                $formMappingRule = $this->createFormMappingRule($formId, 'user');
            }

            $formMappingRule->from = [
                'id' => Arr::get($mappingRule, 'id'),
                'name' => Arr::get($mappingRule, 'name'),
            ];

            $formMappingRule->to = [
                'column' => $column,
                'table' => 'user',
            ];

            $formMappingRule->save();
        }
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

    private function saveRole(int $formId, ?int $role)
    {
        $formMappingRule = $this->getStoredMappingRule($formId, 'role')->first();

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
            $formBuilder->id,
            $request->only('email', 'first_name', 'last_name')
        );

        $this->saveRole($formBuilder->id, $request->get('role'));

        $storedMappingRules = $this->getStoredMappingRule($formBuilder->id, 'form');

        $mappingRule = $request->get('mapping_rules', []);

        $this->saveOptionalFields($formBuilder->id, $mappingRule, $storedMappingRules);

        $this->removeOptionalFields($mappingRule, $storedMappingRules);

        $this->saveCreateUserEmail($formBuilder, $request->get('create_user_email'));

        $this->saveUpdateUserEmail($formBuilder, $request->get('update_user_email'));

        $formBuilder->save();

        $this->generateFlashMessage('Saved');
    }

    public function createOrUpdateUser(Form $formBuilder, FormEntry $formEntry)
    {
        DB::transaction(function () use ($formBuilder, $formEntry) {
            $user = $this->automateUserCreationService->createOrUpdate(
                $formBuilder,
                $formEntry
            );

            if ($user->wasRecentlyCreated) {
                $this->automateUserCreationService->sendUserCreationEmail(
                    $user,
                    $formBuilder
                );
            } else {
                $this->automateUserCreationService->sendUserUpdateEmail(
                    $user,
                    $formBuilder
                );
            }

            $this->automateUserCreationService->markAutomateActionIsDone($formEntry);

            $this->generateFlashMessage("The action ran successfully!");
        });
    }
}
