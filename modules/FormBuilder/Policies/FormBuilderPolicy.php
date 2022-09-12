<?php

namespace Modules\FormBuilder\Policies;

use App\Models\User;
use App\Policies\BasePermissionPolicy;

class FormBuilderPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'form_builder';

    public function notificationRecords(User $user)
    {
        return $user->can($this->basePermission.'.browse')
            && $user->can($this->basePermission.'.edit');
    }
}