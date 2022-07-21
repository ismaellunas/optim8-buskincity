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
            $user->can('space.read')
            || $user->spaces->isNotEmpty()
        );
    }

    public function create(User $user)
    {
        $parentId = request()->get('parent');

        return (
            $user->can('space.add')
            || ($parentId && $this->manage($user, Space::find($parentId)))
            || (!$parentId && $user->spaces->isNotEmpty())
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
        return $user->can('space.read');
    }

    public function accessPreviewPage(User $user, Space $space)
    {
        return (
            $user->can('space.read')
            || $this->manage($user, $space)
        );
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
}
