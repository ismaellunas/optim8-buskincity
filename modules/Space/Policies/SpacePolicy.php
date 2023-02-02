<?php

namespace Modules\Space\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Space\Entities\Space;

class SpacePolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        Space::disableAutoloadTranslations();
    }

    public function viewAny(User $user)
    {
        return (
            $user->can('space.read')
            || $user->spaces->isNotEmpty()
        );
    }

    public function create(User $user)
    {
        return $user->can('space.add');
    }

    public function update(User $user, Space $space)
    {
        return (
            $user->can('space.edit')
            || $this->manage($user, $space)
        );
    }

    public function delete(User $user, Space $space)
    {
        return $user->can('space.delete');
    }

    public function changeParent(User $user)
    {
        return $user->isAdministrator || $user->isSuperAdministrator;
    }

    public function manage(User $user, Space $space): bool
    {
        if ($user->spaces->isEmpty()) {
            return false;
        }

        return (
            $user->spaces->contains('id', $space->id)
            || $user->spaces->contains(function ($currentSpace) use ($space) {
                return $currentSpace->isAncestorOf($space);
            })
        );
    }

    public function managePage(User $user)
    {
        return $user->isAdministrator || $user->isSuperAdministrator;
    }

    public function manageManager(User $user)
    {
        return $user->isAdministrator || $user->isSuperAdministrator;
    }
}
