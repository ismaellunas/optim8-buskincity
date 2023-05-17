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
use Illuminate\Support\Str;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Entities\FormMappingRule;

class AutomateUserCreationService
{
    private $noAdminRoles;

    public function fieldOptionFormatter(Collection $fields, int $formId): Collection
    {
        $properties = ['form_id', 'id', 'label', 'name', 'type'];

        return $fields->map(function ($field) use ($formId, $properties) {
            $field['form_id'] = $formId;
            $field = collect($field);

            return $field->only($properties)->all();
        });
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

        $forms = BaseForm::with(['fieldGroups'])->get();

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

    private function getUserFieldsByRole(mixed $role = null): Collection
    {
        $fields = collect();

        foreach ($this->userForms($role) as $form) {
            $fields = $fields->merge($form->getFields());
        }

        return $fields->filter();
    }

    private function userFieldOptionFormatter(Form $form, FormMappingRule $rule): array
    {
        return [
            'form_id' => $form->id,
            'name' => Arr::get($rule, 'from.name'),
            'id' => Arr::get($rule, 'from.id'),
        ];
    }

    public function getMappingRules(Form $form)
    {
        $mappingRules = $form->userCreationMappingRules;

        return [
            'mandatoryFields' => $mappingRules
                ->where('group', 'user')
                ->whereIn('to.column', $this->mandatoryFields())
                ->mapWithKeys(function ($rule) use ($form) {
                    $key = Arr::get($rule, 'to.column');

                    return [
                        $key => $this->userFieldOptionFormatter($form, $rule)
                    ];
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

                $roleId = Arr::get($role, 'to.role');

                return is_null($roleId) ? null : (int) $roleId;
            },

            'profile_picture' => function () use ($mappingRules, $form) {
                $rule = $mappingRules
                    ->where('group', 'user')
                    ->firstWhere('to.column', 'profile_photo_media_id');

                if ($rule && $rule->from) {
                    return $this->userFieldOptionFormatter($form, $rule);
                }

                return null;
            },
        ];
    }

    private function syncUserMetas(User $user, FormEntry $entry, Collection $mappedRules)
    {
        $defaultLocale = defaultLocale();

        $formFields = $this->fieldOptionFormatter(
            $entry->form->getFields($entry->form),
            $entry->form->id
        );

        $entryMetaKeys = $mappedRules
            ->map(fn ($rule) => $rule->from['name'])
            ->filter()
            ->all();

        $entryMetas = $entry->getMeta($entryMetaKeys);

        $userFields = $this->getUserFieldsByRole($user->roleName);

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

            $className = app(FormBuilderService::class)->getFieldClassName($fromType);

            $fieldClass = new $className(value: $value);

            if ($isTranslated) {
                $fieldClass->translateTo = [$defaultLocale];
            }

            $value = $fieldClass->getMappedValue($userField);

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
    }

    public function mandatoryFields(): Collection
    {
        return collect([
            'email',
            'first_name',
            'last_name',
        ]);
    }

    public function getUserProperties(
        FormEntry $entry,
        EloquentCollection $userRules
    ): array {
        $userProps = [];

        foreach ($this->mandatoryFields() as $column) {
            $rule = $userRules->reverse()->firstWhere('to.column', $column) ?? [];

            $userProps[$column] = $entry->{Arr::get($rule, 'from.name')} ?? null;
        }

        return $userProps;
    }

    public function getMandatoryLabels(Form $form): array
    {
        $labels = [];

        $userRules = $form->mappingUserRules;

        $fields = $form
            ->fieldGroups
            ->map(fn ($fieldGroup) => $fieldGroup->fields)
            ->flatten(1);

        foreach ($this->mandatoryFields() as $column) {
            $field = null;

            $rule = $userRules->reverse()->firstWhere('to.column', $column) ?? [];

            if ($rule && Arr::has($rule, 'from.id')) {
                $field = $fields
                    ->reverse()
                    ->firstWhere('id', Arr::get($rule, 'from.id'));
            }

            $labels[$column] = $field ? $field['label'] : Str::title($column);
        }

        return $labels;
    }

    private function createOrUpdateUser(
        FormEntry $entry,
        EloquentCollection $userRules
    ): User {
        $userProps = $this->getUserProperties($entry, $userRules);

        $user = User::firstWhere('email', $userProps['email']);

        if (! $user) {
            $user = User::factory()->make([
                'email' => $userProps['email'],
                'password' => UserService::hashPassword(uniqid().uniqid()),
                'language_id' => app(LanguageService::class)->getDefaultId(),
            ]);
        }

        $user->first_name = $userProps['first_name'];
        $user->last_name = $userProps['last_name'];
        $user->save();

        return $user;
    }

    private function assignRole(User $user, ?int $roleId)
    {
        if (! $roleId) {

            $user->roles()->detach();

        } elseif ($user->roleId != $roleId) {

            $user->roles()->detach();
            $user->assignRole($roleId);
        }
    }

    private function updateProfilePhoto(
        User $user,
        FormEntry $entry,
        FormMappingRule $rule
    ): ?Media {
        $mediaService = app(MediaService::class);

        $entryMeta = $entry->getMeta($rule->from['name'], []);

        $mediaIds = $entryMeta['mediaId'] ?? [];

        $allowedExtensions = config('constants.extensions.image');

        $firstMedia = Media::whereIn('id', $mediaIds)
            ->get()
            ->first(function ($medium) use ($allowedExtensions) {
                return in_array($medium->extension, $allowedExtensions);
            });

        if ($firstMedia) {
            $media = $mediaService->uploadProfileFromMedia($firstMedia, $user);

            $user->replaceProfilePhoto($media->id);

            $mediaService->setMedially($user, [$media->id]);

            return $media;
        }

        return null;
    }

    public function createOrUpdate(Form $form, FormEntry $entry): User
    {
        $mappedRules = $form->userCreationMappingRules;

        $userRules = $mappedRules->where('group', 'user');

        $user = $this->createOrUpdateUser($entry, $userRules);

        $roleId = Arr::get($mappedRules->firstWhere('group', 'role'), 'to.role');
        $roleId = !is_null($roleId) ? (int) $roleId : null;

        $this->assignRole($user, $roleId);

        $profilePictureRule = $userRules
            ->firstWhere('to.column', 'profile_photo_media_id');

        if ($profilePictureRule) {
            $this->updateProfilePhoto($user, $entry, $profilePictureRule);
        }

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
        if (is_null($this->noAdminRoles)) {
            $this->noAdminRoles = Role::withoutAdmin()->get(['id', 'name']);
        }

        return $this->noAdminRoles->asOptions('id', 'name');
    }

    public function matchedTypes(): array
    {
        return [
            'Country' => \Modules\FormBuilder\Fields\Country::mappingFieldTypes(),
            'Email' => \Modules\FormBuilder\Fields\Email::mappingFieldTypes(),
            'FileDragDrop' => \Modules\FormBuilder\Fields\FileDragDrop::mappingFieldTypes(),
            'Number' => \Modules\FormBuilder\Fields\Number::mappingFieldTypes(),
            'Phone' => \Modules\FormBuilder\Fields\Phone::mappingFieldTypes(),
            'Postcode' => \Modules\FormBuilder\Fields\Postcode::mappingFieldTypes(),
            'Select' => \Modules\FormBuilder\Fields\Select::mappingFieldTypes(),
            'Text' => \Modules\FormBuilder\Fields\Text::mappingFieldTypes(),
            'Textarea' => \Modules\FormBuilder\Fields\Textarea::mappingFieldTypes(),
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
            if (! is_array($fieldGroup->fields)) {
                continue;
            }

            $fieldIds->push(collect($fieldGroup->fields)->map(function ($field) {
                return $field['id'];
            }));
        }

        $fieldIds = $fieldIds->flatten();

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
