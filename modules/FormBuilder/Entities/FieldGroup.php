<?php

namespace Modules\FormBuilder\Entities;

use App\Models\FieldGroup as ModelFieldGroup;

class FieldGroup extends ModelFieldGroup
{
    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FieldGroupFactory::new();
    }

    public function saveFromInputs(array $inputs): void
    {
        $this->title = $inputs['title'];
        $this->order = $inputs['order'];
        $this->fields = $inputs['fields'];
        $this->form_id = $inputs['form_id'];
        $this->save();
    }

    public function syncFieldGroups(array $fieldGroups, int $formId): void
    {
        self::where('form_id', $formId)->delete();

        foreach ($fieldGroups as $index => $fieldGroup) {
            $fieldGroup['order'] = $index + 1;
            $fieldGroup['form_id'] = $formId;

            $fieldGroupClass = new self;
            $fieldGroupClass->saveFromInputs($fieldGroup);
        }
    }
}
