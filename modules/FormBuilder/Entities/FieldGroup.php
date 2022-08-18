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
}
