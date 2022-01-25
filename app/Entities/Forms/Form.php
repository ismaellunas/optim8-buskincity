<?php

namespace App\Entities\Forms;

use App\Models\User;
use Illuminate\Support\Collection;

class Form
{
    public $id;
    public $name;
    public $locations;
    public $model;
    public $routeName;
    public $title;
    public $visibility;
    public $fields;
    public $order;

    public User $author;
    public $formLocation;

    protected $data;

    public function __construct($id, array $data, User $author = null)
    {
        $this->id = $id;
        $this->data = $data;

        $this->name = $data['name'];
        $this->title = $data['title'] ?? null;
        $this->order = $data['order'] ?? 0;
        $this->locations = $data['locations'] ?? [];

        if ($author) {
            $this->author = $author;
        }

        $this->setFields($data['fields']);

        $this->visibility = $data['visibility'] ?? [];
    }

    public function schema(array $values = []): array
    {
        $fields = $this->getFieldSchema($values);

        return [
            'name' => $this->name,
            'title' => $this->title,
            'order' => $this->order,
            'fields' => $fields,
            'buttons' => $this->buttons(),
        ];
    }

    protected function getFieldClassName($type): string
    {
        return "\\App\\Entities\\Forms\\Fields\\".$type;
    }

    protected function setFields(array $fields = [])
    {
        $fieldCollection = collect();

        foreach ($fields as $name => $field) {
            $className = $this->getFieldClassName($field['type']);

            if (class_exists($className)) {
                $fieldObject = new $className($name, $field);

                if ($fieldObject->canBeAccessed($this->author)) {
                    $fieldCollection->put($name, $fieldObject);
                }
            }
        }

        $this->fields = $fieldCollection;
    }

    protected function getFieldSchema(array $values = []): Collection
    {
        $schema = collect();

        foreach ($this->fields as $name => $field) {
            $fieldSchema = $field->schema();

            if (array_key_exists($name, $values)) {
                $fieldSchema['value'] = $values[$name];
            }

            $schema->put($name, $fieldSchema);
        }

        return $schema;
    }

    protected function buttons(): array
    {
        return [
            [
                'label' => 'Submit'
            ]
        ];
    }

    public function rules(): array
    {
        $rules = [];

        foreach ($this->fields as $field) {
            $rules = array_merge($rules, $field->validationRules());
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->fields as $field) {
            $attributes = array_merge($attributes, $field->getLabels());
        }

        return $attributes;
    }

    public function canBeAccessed(): bool
    {
        $author = $this->author;
        $roles = $this->visibility['roles'] ?? [];

        if (is_null($author)) {
            return false;
        }

        if ($author->hasRole([
            config('permission.super_admin_role'),
            'Administrator'
        ])) {
            return true;
        }

        if (!empty($roles)) {
            return $author->hasRole($roles);
        }

        return true;
    }
}
