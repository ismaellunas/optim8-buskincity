<?php

namespace Modules\Space\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Space\Entities\Space;

class SpacePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return (
            $user->can('space.browse')
            || $user->spaces->isNotEmpty()
        );
    }

    public function create(User $user)
    {
        return (
            $user->can('space.add')
            || $user->spaces->contains(fn ($space) => $space->isParentable)
        );
    }

    public function update(User $user, Space $space)
    {
        return (
            $user->can('space.edit')
            || $this->manage($user, $space)
        );
    }

    private function manageAncestorOfSpace(User $user, Space $space)
    {
        return $user->spaces->contains(
            fn ($currentSpace) => $currentSpace->isAncestorOf($space)
        );
    }

    public function delete(User $user, Space $space)
    {
        return (
            $user->can('space.delete')
            || $this->manageAncestorOfSpace($user, $space)
        );
    }

    public function changeParent(User $user, Space $space)
    {
        return (
            $user->can('space.edit')
            || (
                $user->spaces->isNotEmpty()
                && !$user->spaces->contains('id', $space->id)
            )
        );
    }

    public function manage(User $user, Space $space): bool
    {
        return (
            $user->spaces->contains('id', $space->id)
            || $this->manageAncestorOfSpace($user, $space)
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

    public function totalSpaceByTypeWidget(User $user, int $typeId)
    {
        if ($user->can('space.read')) {
            return true;
        }

        $spaces = $user->spaces;

        if ($user->spaces->isEmpty()) {
            return false;
        }

        return Space::where(function ($query) use ($spaces) {
                foreach ($spaces as $key => $space) {
                    $boolean = $key == 0 ? 'and' : 'or';
                    $query->whereDescendantOrSelf($space, $boolean);
                }
            })
            ->whereHas('type', fn ($query) => $query->where('id', $typeId))
            ->exists();
    }
}
