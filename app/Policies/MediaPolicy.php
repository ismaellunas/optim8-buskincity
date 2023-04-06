<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MediaPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'media';

    public function update(User $user, Model $media)
    {
        return (
            parent::update($user, $media)
            && $media->isDefaultType
            && (
                $media->user_id == $user->id
                || $this->manageOtherMedia($user)
            )
        );
    }

    public function delete(User $user, Model $media)
    {
        return (
            parent::delete($user, $media)
            && $media->isDefaultType
            && (
                $media->user_id == $user->id
                || $this->manageOtherMedia($user)
            )
        );
    }

    public function manageOtherMedia(User $user)
    {
        return $user->can($this->basePermission.'.other_users');
    }
}
