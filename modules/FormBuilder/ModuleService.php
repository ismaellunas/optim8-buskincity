<?php

namespace Modules\FormBuilder;

class ModuleService
{
    public static function permissions()
    {
        return config('formbuilder.permissions');
    }
}