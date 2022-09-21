<?php

namespace Modules\Space\Policies;

use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Space\Entities\Space;

class SpacePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return (
            $user->can('space.read')
            || $user->spaces->isNotEmpty()
        );
    }

    public function create(User $user, ?Space $parentSpace = null)
    {
        if (is_null($parentSpace)) {
            $parentSpace = Space::find(request()->get('parent'));
        }

        return (
            $user->can('space.add')
            || ($parentSpace && $this->manage($user, $parentSpace))
            || (!$parentSpace && $user->spaces->isNotEmpty())
        );
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
        return (
            $user->can('space.delete')
            || $this->manage($user, $space)
        );
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
