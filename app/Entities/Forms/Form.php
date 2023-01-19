<?php

namespace App\Entities\Forms;

use App\Entities\Forms\Fields\TranslatableField;
use App\Models\Form as ModelForm;
use App\Models\FieldGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Form
{
    public $button;
    public $fieldGroups;
    public $fields;
    public $id;
    public $locations;
    public $model;
    public $name;
    public $order;
    public $routeName;
    public $title;
    public $visibility;

    public ?User $author = null;
    public $formLocation;

    protected $originLanguage = null;

    private $key = null;
    private $rawFields;

    public function __construct(
        ModelForm $form,
        User $author = null
    ) {
        $this->id = $form->id;

        $this->key = $form->key;
        $this->name = $form->name;
        $this->order = $form->order;

        $settings = $form->setting;

        $this->locations = $settings['locations'] ?? [];
        $this->button = $settings['button'] ?? [];

        if ($author) {
            $this->author = $author;
            $this->originLanguage = $author->origin_language_code;
        }

        if ($form->fieldGroups->isNotEmpty()) {
            $this->setFieldGroups($form->fieldGroups);

            $this->setRawFields($form->fieldGroups);
        }

        $this->visibility = $settings['visibility'] ?? [];
    }

    public function schema(array $values = []): array
    {
        $fieldGroups = $this->getFieldGroupSchema($values);

        return [
            'key' => $this->key,
            'name' => $this->name,
            'order' => $this->order,
            'fieldGroups' => $fieldGroups,
            'button' => $this->button,
        ];
    }

    protected function getFieldClassName($type): string
    {
        return "\\App\\Entities\\Forms\\Fields\\".$type;
    }

    protected function setFieldGroups(Collection $fieldGroups): void
    {
        $fieldGroupCollection = collect();

        $this->fields = collect();

        foreach ($fieldGroups as $fieldGroup) {
            $fields = $this->getFields($fieldGroup->fields);

            $this->fields = $this->fields->merge($fields);

            $fieldGroupCollection->push([
                'title' => $fieldGroup->title ?? null,
                'fields' => $fields,
                'order' => $fieldGroup->order,
            ]);
        }

        $this->fieldGroups = $fieldGroupCollection
            ->sortBy('order')
            ->values();
    }

    protected function getFields(array $fields = []): Collection
    {
        $fieldCollection = collect();

        foreach ($fields as $name => $field) {
            $className = $this->getFieldClassName($field['type']);
            $fieldName = $field['name'];

            if (class_exists($className)) {
                $fieldObject = new $className($fieldName, $field);

                if ($fieldObject instanceof TranslatableField) {
                    $fieldObject->setOriginLanguage($this->originLanguage);
                }

                if ($fieldObject->canBeAccessed($this->author)) {
                    $fieldCollection->put($fieldName, $fieldObject);
                }
            }
        }

        return $fieldCollection;
    }

    private function setRawFields(Collection $fieldGroups): void
    {
        $this->rawFields = [];

        foreach ($fieldGroups as $fieldGroup) {
            $this->rawFields = [
                ...$this->rawFields,
                ...$fieldGroup->fields,
            ];
        }
    }

    protected function getFieldGroupSchema(array $storedValues = []): Collection
    {
        $schema = collect();

        foreach ($this->fieldGroups as $fieldGroup) {
            $fields = collect();

            foreach ($fieldGroup['fields'] as $name => $field) {
                $storedValue = $field->findStoredValue($storedValues);

                if ($storedValue) {
                    $field->storedValue = $storedValue;
                }

                $fieldSchema = $field->schema();

                $fields->put($name, $fieldSchema);
            }

            $schema->push([
                'title' => $fieldGroup['title'],
                'fields' => $fields,
            ]);
        }

        return $schema;
    }

    public function rules($location): array
    {
        $rules = [];

        $storedValues = $location->getValues($this->fields->keys())->all();

        foreach ($this->fields as $field) {

            $storedValue = $field->findStoredValue($storedValues);

            if (! is_null($storedValue)) {
                $field->storedValue = $storedValue;
            }

            $rules = array_merge($rules, $field->validationRules());
        }

        return $rules;
    }

    public function attributes($inputs = null): array
    {
        $attributes = [];

        foreach ($this->fields as $field) {
            $attributes = array_merge($attributes, $field->validationAttributes($inputs));
        }

        return $attributes;
    }

    public function canBeAccessed(): bool
    {
        $author = $this->author;
        $roles = $this->visibility['roles'] ?? [];

        if (!empty($roles)) {
            return $author->hasRole($roles);
        }

        return true;
    }

    public function canBeAccessedByLocation(string $locationRoute = null): bool
    {
        $author = $this->author;
        $locations = $this->locations ?? [];

        foreach ($locations as $location) {
            if (
                $location['name'] == $locationRoute
                && !empty($location['visibility']['roles'])
                && !is_null($author)
            ) {
                return $author->hasRole($location['visibility']);
            }
        }

        return true;
    }

    public function setFieldWithValues($metas): array
    {
        $values = collect();

        foreach ($this->rawFields as $field) {
            $className = $this->getFieldClassName($field['type']);
            $fieldName = $field['name'];

            if (class_exists($className)) {
                $fieldObject = new $className($fieldName, $field);

                $fieldValues = [
                    "type" => $field['type'],
                    "label" => $field['label'],
                    "value" => array_key_exists($fieldName, $metas)
                        ? $metas[$fieldName]
                        : null,
                ];

                if (
                    $fieldObject instanceof TranslatableField
                    && isset($field['translated'])
                ) {
                    $fieldValues['is_translated'] = $field['translated'];
                }

                $values->push($fieldValues);
            }
        }

        return $values->all();
    }
}
