<?php

namespace Modules\FormBuilder\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Modules\FormBuilder\Entities\FormEntry;

class FormEntryPolicy extends FormBuilderPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
    }

    public function markAsRead(User $user, FormEntry $model)
    {
        return (
            $user->can($this->basePermission.'.edit')
            && !$model->isRead
            && !$model->trashed()
        );
    }

    public function markAsUnread(User $user, FormEntry $model)
    {
        return (
            $user->can($this->basePermission.'.edit')
            && $model->isRead
            && !$model->trashed()
        );
    }

    public function delete(User $user, Model $model)
    {
        return (
            !$model->trashed()
            && $user->can($this->basePermission.'.delete')
        );
    }

    public function restore(User $user, Model $model)
    {
        return (
            $model->trashed()
            && $user->can($this->basePermission.'.delete')
        );
    }

    public function forceDelete(User $user, FormEntry $model)
    {
        return (
            $model->trashed()
            && $user->can($this->basePermission.'.delete')
        );
    }
}
