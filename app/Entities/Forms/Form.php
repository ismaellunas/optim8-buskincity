<?php

namespace App\Entities\Forms;

use App\Contracts\ArrayValueFieldInterface;
use App\Models\Form as FormModel;
use Illuminate\Support\Collection;

class Form
{
    public $id;
    public $name;
    public $model;

    protected $data;
    protected $fields;

    public function __construct($id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
        $this->name = $data['name'];
        $this->fields = $this->getFields($data['fields']);
    }

    public function schema(array $values = []): array
    {
        $fields = $this->getFieldSchema($values);

        return [
            'name' => $this->name,
            'fields' => $fields,
            'buttons' => $this->buttons(),
        ];
    }

    protected function getFieldClassName($type): string
    {
        return "\\App\\Entities\\Forms\\Fields\\".$type;
    }

    protected function getFields($fields): Collection
    {
        $fieldCollection = collect();

        foreach ($fields as $name => $field) {
            $className = $this->getFieldClassName($field['type']);

            if (class_exists($className)) {
                $fieldCollection->put($name, new $className($name, $field));
            }
        }

        return $fieldCollection;
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

        foreach ($this->fields as $name => $field) {
            $rules[$name] = $field->validationRules();

            if ($field instanceof ArrayValueFieldInterface) {
                $rules[$name.".*"] = $field->arrayValidationRules();
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->fields as $name => $field) {
            $attributes[$name] = $field->label;
        }

        return $attributes;
    }
}
