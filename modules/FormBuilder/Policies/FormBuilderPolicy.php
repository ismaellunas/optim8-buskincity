<?php

namespace Modules\FormBuilder\Policies;

use App\Policies\BasePermissionPolicy;

class FormBuilderPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'form_builder';
}