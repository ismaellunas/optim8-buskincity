<?php

namespace App\Services;

use App\Entities\Forms\Form;
use App\Models\{
    FieldGroup,
    User,
};
use Illuminate\Support\Collection;

class FormService
{
    private $routeToLocationMaps = [
        'admin.profile.show' => 'UserProfileLocation',
        'admin.users.edit' => 'UserEditLocation',
    ];

    private $formBasePath = 'App\\Entities\\Forms';
    private $formLocationBasePath = "App\\Entities\\Forms\\Locations";

    public function getFormByName($name, User $author = null): ?Form
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

    private function getFormClassName(?string $type): string
    {
        return $this->formBasePath."\\".$type.'Form';
    }

    public function getFormsOnRoute(
        string $locationRoute,
        User $author = null
    ): Collection {

        $forms = collect();

        $models = FieldGroup::whereJsonContains('data->locations', [ ['name' => $locationRoute] ])
            ->get();

        foreach ($models as $model) {

            $className = $this->getFormClassName($model->type);

            $form = new $className($model->id, $model->data, $author);

            if ($form->canBeAccessedByLocation($locationRoute)) {
                $form->model = $model;

                $forms->put($form->name, $form);
            }

        }

        return $forms;
    }

    public function getFormLocation(string $routeName, int $entityId = null)
    {
        $locationClass = $this->routeToLocationMaps[ $routeName ];
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
            $options = [
                'locations' => $form->locations
            ];

            if (
                $form->canBeAccessed()
                && $location->canBeAccessedByEntity($options)
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
        string $routeName,
        User $actor,
        int $entityId = null
    ) {

        $formLocation = $this->getFormLocation($routeName, $entityId);

        $forms = $this->getFormsOnRoute($routeName, $actor);

        foreach ($forms as $form) {
            $formLocation->save($form->fields, $inputs);
        }
    }

    public function getSchemas(
        string $routeName,
        User $actor,
        int $entityId = null
    ): Collection {

        $formLocation = $this->getFormLocation($routeName, $entityId);

        $forms = $this->getFormsOnRoute($routeName, $actor);

        $schemas = collect();

        foreach ($forms as $form) {
            $options = [
                'locations' => $form->locations
            ];

            if (
                $form->canBeAccessed()
                && $formLocation->canBeAccessedByEntity($options)
            ) {
                $values = $formLocation->getValues($form->fields->keys());

                $schema = $form->schema($values->all());

                $schemas->push($schema);
            }

        }

        return $schemas;
    }

    public function getFieldGroupValues(User $user): array
    {
        $values = collect();

        $models = FieldGroup::all();

        foreach ($models as $model) {
            $className = $this->getFormClassName($model->type);

            $form = new $className($model->id, $model->data, $user);

            $metas = $user->getMetas($form->fields->keys()->all());

            $values->put(
                $form->title,
                $form->setFieldWithValues($metas->all())
            );
        }

        return $values->all();
    }
}
