<?php

namespace Modules\FormBuilder\Services;

use App\Models\Form as BaseForm;
use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use App\Services\LanguageService;
use App\Services\MediaService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\FormBuilder\Emails\AutomateUserCreationEmail;
use Modules\FormBuilder\Emails\AutomateUserUpdateEmail;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Entities\FormMappingRule;

class AutomateUserCreationService
{
    public function getFields(BaseForm $formBuilder, bool $isOption = true): Collection
    {
        $fields = collect();

        foreach ($formBuilder->fieldGroups as $fieldGroup) {
            foreach ($fieldGroup->fields as $field) {
                if ($isOption) {
                    $field['form_id'] = $formBuilder->id;
                    $field = collect($field);
                    $fields->push($field->only(
                        'form_id', 'id', 'label', 'name', 'type'
                    ));
                } else {
                    $fields->push($field);
                }
            }
        }

        return $fields;
    }

    private function userForms(mixed $role): EloquentCollection
    {
        $forms = BaseForm::whereNull('type')->with('fieldGroups')->get();

        if ($role) {
            return $forms->filter(function ($form) use ($role) {
                return collect(Arr::get($form->setting, 'locations', []))
                    ->filter(function ($location) use ($role) {
                        $name = Arr::get($location, 'name');
                        $visibility = Arr::get($location, 'visibility.roles', []);

                        return (
                            $name == 'admin.profile.show'
                            && in_array($role, $visibility)
                        );
                    })
                    ->isNotEmpty();
            });
        }

        return $forms;
    }

    public function getUserFields(): Collection
    {
        $fields = collect();
        $roleOptions = $this->getRoleOptions();

        $forms = BaseForm::whereNull('type')->with(['fieldGroups'])->get();

        foreach ($forms as $form) {
            $location = collect(Arr::get($form->setting, 'locations', []))
                ->first(function ($location) {
                    return Arr::get($location, 'name') == 'admin.profile.show';
                });

            if (! $location) {
                continue;
            }

            $roleIds = collect(Arr::get($location, 'visibility.roles', []))
                ->map(function ($role) use ($roleOptions) {

                    $filteredRole = $roleOptions->first(function ($roleOption) use ($role) {
                        return $roleOption['value'] == $role;
                    });

                    return $filteredRole['id'];
                })
                ->filter()
                ->all();

            $notInRoleIds = collect(Arr::get($location, 'visibility.not_in_roles', []))
                ->map(function ($role) use ($roleOptions) {

                    $filteredRole = $roleOptions->first(function ($roleOption) use ($role) {
                        return $roleOption['value'] == $role;
                    });

                    return $filteredRole['id'];
                })
                ->filter()
                ->all();

            foreach ($form->fieldGroups as $fieldGroup) {
                foreach ($fieldGroup->fields as $field) {
                    $field = collect($field)->only('id', 'label', 'name', 'type');

                    $field->put('form_id', $form->id);
                    $field->put('roles', $roleIds);
                    $field->put('not_in_roles', $notInRoleIds);

                    $fields->push($field);
                }
            }
        }

        return $fields;
    }

    private function getUserFieldsByRole(bool $isOption = true, mixed $role = null): Collection
    {
        $fields = collect();

        foreach ($this->userForms($role) as $form) {
            $fields = $fields->merge($this->getFields($form, $isOption));
        }

        return $fields->filter();
    }

    public function getMappingRules(BaseForm $form)
    {
        $mappingRules = FormMappingRule::where('form_id', $form->id)
            ->where('type', 'automate_user_creation')
            ->get();

        return [
            'mandatoryFields' => $mappingRules
                ->where('group', 'user')
                ->mapWithKeys(function ($field) use ($form) {
                    $key = Arr::get($field, 'to.column');

                    return [$key => [
                        'form_id' => $form->id,
                        'name' => Arr::get($field, 'from.name'),
                        'id' => Arr::get($field, 'from.id'),
                    ]];
                }),

            'optionalFields' => $mappingRules
                ->where('group', 'form')
                ->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'from' => Arr::get($field, 'from'),
                        'to' => Arr::get($field, 'to'),
                    ];
                })
                ->values(),

            'role' => function () use ($mappingRules) {
                $role = $mappingRules->firstWhere('group', 'role');

                return $role ? (int) $role->to['role'] : null;
            },
        ];
    }

    private function syncUserMetas(User $user, FormEntry $entry, Collection $mappedRules)
    {
        $defaultLocale = defaultLocale();

        $formFields = $this->getFields($entry->form);

        $entryMetaKeys = $mappedRules
            ->map(fn ($rule) => $rule->from['name'])
            ->filter()
            ->all();

        $entryMetas = $entry->getMeta($entryMetaKeys);

        $userFields = $this->getUserFieldsByRole(false, $user->roleName);

        foreach ($mappedRules as $rule) {
            $value = $entryMetas[$rule->from['name']];

            $formField = $formFields->last(function ($field) use ($rule) {
                return $field['id'] == $rule->from['id'];
            });

            $userField = $userFields->last(function ($field) use ($rule) {
                return $field['name'] == $rule->to['name'];
            });

            $fromType = $formField['type'];
            $toName = $rule->to['name'];
            $isTranslated = Arr::get($userField, 'translated', false) == true;

            if (! $userField || ! $formField) {
                continue;
            }

            $className = "Modules\\FormBuilder\\Fields\\".Str::studly($fromType);
            $fieldClass = new $className();
            $fieldClass->value = $value;

            if ($isTranslated) {
                $fieldClass->translateTo = [$defaultLocale];
            }

            if ($fromType == 'FileDragDrop') {
                $fieldClass->mappedValueFormatter = function ($value) {
                    return Arr::get($value, 'mediaId');
                };
            }

            $value = $fieldClass->getMappedValue($userField['type']);

            if ($fromType == 'FileDragDrop' && $value) {
                $media = Media::whereIn('id', $value)->get();
                $duplicatedMediaIds = [];

                foreach ($media as $medium) {
                    $duplicatedMedia = app(MediaService::class)
                        ->uploadUserMetaFromMedia($medium, $user);

                    $duplicatedMediaIds[] = $duplicatedMedia->id;
                }

                $value = $duplicatedMediaIds;
            }

            if (is_array($value) && $fromType == 'Phone') {
                foreach ($value as $keyPart => $valuePart) {
                    $user->setMeta(
                        Str::replaceFirst(':fieldname:', $toName, $keyPart),
                        $valuePart
                    );
                }
            } else {
                $user->setMeta($toName, $value);
            }
        }

        $user->saveMetas();

        //\Log::debug( $mappedRules->map(fn ($rule) => $rule->to['name'])->all());
    }

    public function sendUserCreationEmail(User $user, Form $form)
    {
        Mail::to($user)->queue(
            (new AutomateUserCreationEmail($user, $form))->afterCommit()
        );
    }

    public function sendUserUpdateEmail(User $user, Form $form)
    {
        Mail::to($user)->queue(
            (new AutomateUserUpdateEmail($user, $form))->afterCommit()
        );
    }

    public function mandatoryFields(): Collection
    {
        return collect([
            'email',
            'first_name',
            'last_name',
        ]);
    }

    public function createOrUpdate(Form $form, FormEntry $entry): User
    {
        $mappedRules = $form->userCreationMappingRules;

        $userRules = $mappedRules->where('group', 'user');

        // Create/Update User
        $mandatoryProps = $this
            ->mandatoryFields()
            ->mapWithKeys(fn ($fieldName) => [$fieldName => null])
            ->all();

        foreach (array_keys($mandatoryProps) as $column) {
            $rule = $userRules->firstWhere('to.column', $column);

            $mandatoryProps[$column] = $entry->{$rule->from['name']};
        }

        $user = User::firstWhere('email', $mandatoryProps['email']);

        if (! $user) {
            $user = User::factory()->make([
                'email' => $mandatoryProps['email'],
                'password' => UserService::hashPassword(uniqid().uniqid()),
                'language_id' => app(LanguageService::class)->getDefaultId(),
            ]);
        }

        $user->first_name = $mandatoryProps['first_name'];
        $user->last_name = $mandatoryProps['last_name'];
        $user->save();

        // Assign Role
        $roleId = null;
        $roleRule = $mappedRules->firstWhere('group', 'role');

        if ($roleRule) {
            $roleId = $roleRule->to['role'] ? (int) $roleRule->to['role'] : null;
        }

        if (! $roleId) {

            $user->roles()->detach();

        } elseif ($user->roleId != $roleId) {

            $user->roles()->detach();
            $user->assignRole($roleId);
        }

        $user->forgetCachedPermissions();

        $this->syncUserMetas($user, $entry, $mappedRules->where('group', 'form'));

        return $user;
    }

    public function markAutomateActionIsDone(FormEntry $formEntry)
    {
        $formEntry->setMeta('automate_user_creation_at', now());
        $formEntry->save();
    }

    public function getRoleOptions(): Collection
    {
        return Role::withoutAdmin()
            ->get(['id', 'name'])
            ->asOptions('id', 'name');
    }

    public function matchedTypes(): array
    {
        return [
            'Country' => ['Country', 'Text', 'Textarea'],
            'Email' => ['Email', 'Text', 'Textarea'],
            'FileDragDrop' => ['FileDragDrop'],
            'Number' => ['Number', 'Text', 'Textarea'],
            'Phone' => ['Phone', 'Text', 'Textarea'],
            'Postcode' => ['Postcode', 'Text', 'Textarea'],
            'Select' => ['Select', 'Text', 'Textarea'],
            'Text' => ['Text', 'Textarea', 'Video'],
            'Textarea' => ['Textarea'],
            'Video' => ['Video', 'Text', 'Textarea'],
        ];
    }

    public function mandatoryMatchedTypes(): array
    {
        return [
            'email' => ['Text', 'Email'],
            'first_name' => ['Text'],
            'last_name' => ['Text'],
        ];
    }

    public static function emailTags(User $user = null): array
    {
        return [
            'email' => $user->email ?? null,
            'first_name' => $user->first_name ?? null,
            'last_name' => $user->last_name ?? null,
            'app_name' => config('app.name'),
        ];
    }

    public function swapTagWithValue(User $user, ?string $text = null): ?string
    {
        $swapLists = [];

        foreach (self::emailTags($user) as $tag => $value) {
            $swapLists['{'.$tag.'}'] = $value;
        }

        return Str::swap($swapLists, $text);
    }

    public function haveAllMandatoryFieldsBeenProvided(Form $form): bool
    {
        $providedMandatoryColumns = $form
            ->userCreationMappingRules
            ->where('group', 'user')
            ->map(fn ($rule) => $rule->to['column'] ?? null)
            ->filter()
            ->all();

        return $this
            ->mandatoryFields()
            ->diff($providedMandatoryColumns)
            ->isEmpty();
    }

    public function removeUntrackedRules(Form $form): Collection
    {
        $fieldIds = collect();

        foreach ($form->fieldGroups as $fieldGroup) {
            if (! is_array( $fieldGroup->fields)) {
                continue;
            }

            $fieldIds = collect($fieldGroup->fields)->map(function ($field) {
                return $field['id'];
            });
        }

        $untrackedRules = $form
            ->userCreationMappingRules
            ->filter(function ($rule) use ($fieldIds) {
                return (
                    Arr::has($rule->from, 'id')
                    && ! $fieldIds->contains($rule->from['id'])
                );
            });

        foreach ($untrackedRules as $untrackedRule) {
            $untrackedRule->delete();
        }

        return $untrackedRules;
    }
}
