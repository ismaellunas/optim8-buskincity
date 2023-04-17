<?php

namespace App\Services;

use App\Entities\Form as EntityForm;
use App\Models\{
    Form,
    FieldGroup,
    User,
};
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class FormService
{
    private $routeToLocationMaps = [
        'admin.profile.show' => 'UserProfileLocation',
        'admin.users.edit' => 'UserEditLocation',
    ];

    private $formBasePath = 'App\\Entities\\Forms';
    private $formLocationBasePath = "App\\Entities\\Forms\\Locations";

    public function getFormByName($name, User $author = null): ?EntityForm
    {
        $model = FieldGroup::name($name)->first();

        if ($model) {
            $className = $this->getFormClassName($model->type);

            $form = new $className($model->id, $model->data, $author);

            $form->model = $model;

            return $form;
        }

        return null;
    }

    private function getFormClassName(?string $type = null): string
    {
        return $this->formBasePath."\\".$type.'Form';
    }

    public function getFormsOnRoute(
        string $locationRoute,
        User $author = null
    ): Collection {

        $forms = collect();

        $models = Form::with('fieldGroups')
            ->locationRoute($locationRoute)
            ->get();

        foreach ($models as $model) {

            $className = $this->getFormClassName();

            $form = new $className($model, $author);

            if ($form->canBeAccessedByLocation($locationRoute)) {
                $form->model = $model;

                $forms->put($form->name, $form);
            }

        }

        return $forms;
    }

    public function getFormLocation(string $routeName, int $entityId = null)
    {
        $locationClass = $this->routeToLocationMaps[ $routeName ] ?? null;
        $locationClass = $this->formLocationBasePath.'\\'.$locationClass;

        if (class_exists($locationClass)) {
            return new $locationClass($entityId);
        }

        return null;
    }

    public function getRules(Collection $forms, $location): array
    {
        $rules = [];

        foreach ($forms as $form) {

            if (
                $form->canBeAccessed()
                && $location->canBeAccessedByEntity($form->locations)
            ) {
                $rules = array_merge($rules, $form->rules($location));
            }
        }

        return $rules;
    }

    public function getAttributes(Collection $forms, $inputs = null): array
    {
        $attributes = [];

        foreach ($forms as $form) {
            $attributes = array_merge($attributes, $form->attributes($inputs));
        }

        return $attributes;
    }

    public function saveValues(
        array $inputs,
        string $key,
        string $routeName,
        User $actor,
        int $entityId = null
    ) {

        $formLocation = $this->getFormLocation($routeName, $entityId);

        if (
            ! $formLocation->canBeAccessedBy($actor)
            || $formLocation->isEntityTrashed()
        ) {
            $this->abortAction();
        }

        $forms = $this->getFormsOnKeyAndRoute($key, $routeName, $actor);

        foreach ($forms as $form) {
            $formLocation->save($form->fields, $inputs);
        }
    }

    public function getSchemas(
        string $routeName,
        ?User $actor = null,
        int $entityId = null
    ): Collection {

        $formLocation = $this->getFormLocation($routeName, $entityId);

        $forms = $this->getFormsOnRoute($routeName, $actor);

        $schemas = collect();

        if (!$formLocation->canBeAccessedBy($actor)) {
            $this->abortAction();
        }

        foreach ($forms as $form) {

            if (
                $form->canBeAccessed()
                && $formLocation->canBeAccessedByEntity($form->locations)
            ) {
                $values = collect();

                if ($actor) {
                    $values = $formLocation->getValues($form->fields->keys());
                }

                $schema = $form->schema($values->all());

                $schemas->push($schema);
            }

        }

        return $schemas;
    }

    private function abortAction(): void
    {
        abort(Response::HTTP_FORBIDDEN);
    }

    public function getFormsOnKeyAndRoute(
        string $key,
        string $locationRoute,
        User $author = null
    ): Collection {

        $forms = collect();

        $model = Form::with('fieldGroups')
            ->key($key)
            ->locationRoute($locationRoute)
            ->first();

        if ($model) {
            $className = $this->getFormClassName();

            $form = new $className($model, $author);

            if ($form->canBeAccessedByLocation($locationRoute)) {
                $form->model = $model;

                $forms->put($form->name, $form);
            }
        }

        return $forms;
    }
}
