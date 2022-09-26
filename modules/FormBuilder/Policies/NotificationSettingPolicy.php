<?php

namespace Modules\FormBuilder\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationSettingPolicy extends FormBuilderPolicy
{
    public function viewAny(User $user)
    {
        return (
            parent::viewAny($user)
            && $user->can($this->basePermission.'.edit')
        );
    }

    public function create(User $user)
    {
        return (
            parent::create($user)
            && $user->can($this->basePermission.'.edit')
        );
    }

    public function delete(User $user, Model $model)
    {
        return (
            parent::delete($user, $model)
            && $user->can($this->basePermission.'.edit')
        );
    }
}
