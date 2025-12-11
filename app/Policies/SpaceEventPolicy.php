<?php

namespace App\Policies;

use Modules\Space\Entities\SpaceEvent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpaceEventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->can('city.manage_events') 
            || $user->can('space.browse');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SpaceEvent $spaceEvent)
    {
        if ($user->can('space.read')) {
            return true;
        }

        if ($this->canManageEventCity($user, $spaceEvent)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->can('city.manage_events') 
            || $user->can('space.add');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SpaceEvent $spaceEvent)
    {
        if ($user->can('space.edit')) {
            return true;
        }

        if ($this->canManageEventCity($user, $spaceEvent)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SpaceEvent $spaceEvent)
    {
        if ($user->can('space.delete')) {
            return true;
        }

        if ($this->canManageEventCity($user, $spaceEvent)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SpaceEvent $spaceEvent)
    {
        if ($user->can('space.edit')) {
            return true;
        }

        if ($this->canManageEventCity($user, $spaceEvent)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SpaceEvent $spaceEvent)
    {
        return $user->can('space.delete');
    }

    /**
     * Check if user can manage events in the event's city.
     */
    private function canManageEventCity(User $user, SpaceEvent $spaceEvent): bool
    {
        if (!$user->hasPermissionTo('city.manage_events')) {
            return false;
        }

        if (!$spaceEvent->city_id) {
            return false;
        }

        return $user->isCityAdmin($spaceEvent->city_id);
    }
}
