<?php

namespace Modules\FormBuilder\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Services\AutomateUserCreationService;

class FormEntryPolicy extends FormBuilderPolicy
{
    use HandlesAuthorization;

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

    public function automateUserCreation(User $user, FormEntry $model)
    {
        $isPermitted = (
            !$model->deleted_at
            && $user->can('form_builder.automate_user_creation')
            && empty($model->automate_user_creation_at)
        );

        $haveAllMandatoryRules = app(AutomateUserCreationService::class)
            ->haveAllMandatoryFieldsBeenProvided($model->form);

        return ($isPermitted && $haveAllMandatoryRules);
    }
}
