<?php

namespace Modules\FormBuilder\Entities;

use App\Models\FieldGroup as ModelFieldGroup;
use Illuminate\Database\Eloquent\Builder;

class FieldGroup extends ModelFieldGroup
{
    public const TYPE = 'form_builder';

    protected function customNewQuery($newQuery): Builder
    {
        return $newQuery->type(self::TYPE);
    }

    public function saveFromInputs(array $inputs): void
    {
        $this->name = $inputs['name'];
        $this->data = $inputs['fields'];
        $this->title = $inputs['key'];
        $this->type = self::TYPE;
        $this->save();
    }

    public function entries()
    {
        return $this->hasMany(FieldGroupEntry::class, 'field_group_id');
    }
}
