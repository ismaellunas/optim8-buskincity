<?php

namespace Modules\Space\Policies;

use App\Models\User;
use App\Services\UserScopeService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Traits\Macroable;
use Modules\Space\Entities\Space;

class SpacePolicy
{
    use HandlesAuthorization;
    use Macroable;

    public function before(User $user, $ability)
    {
        // bookAProduct must still verify the space has a published product
        // (city/country pages have no product — admin bypass caused null errors).
        if ($ability === 'bookAProduct') {
            return null;
        }

        if ($user->isAdministrator) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return (
            $user->can('space.browse')
            || $user->spaces->isNotEmpty()
            || $user->hasRole('city_administrator')
            || $user->isSpecialEventsAdmin()
        );
    }

    public function create(User $user)
    {
        return (
            $user->can('space.add')
            || $user->spaces->contains(fn ($space) => $space->isParentable)
            || $user->hasRole(config('permission.role_names.city_admin'))
            || $user->isSpecialEventsAdmin()
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
            || $this->managesCityPublicPage($user, $space)
        );
    }

    /**
     * Whether the user may manage page-builder content for a space.
     *
     * Global admins are handled by {@see before()}. City admins may manage the
     * public page of their assigned city (City-type space only).
     *
     * @param  Space|string|null  $space  When omitted, returns true if the city
     *                                     admin has any scoped city (UI flags).
     */
    public function managePage(User $user, $space = null): bool
    {
        if ($space instanceof Space) {
            return $this->managesCityPublicPage($user, $space);
        }

        if ($user->isCityAdministrator()) {
            return ! empty(app(UserScopeService::class)->scopedCityIds($user));
        }

        return false;
    }

    /**
     * City admins may edit the City-type space node for cities in their scope.
     */
    private function managesCityPublicPage(User $user, Space $space): bool
    {
        if (! $user->isCityAdministrator()) {
            return false;
        }

        $space->loadMissing('type');

        if ($space->type?->name !== 'City' || ! $space->city_id) {
            return false;
        }

        return app(UserScopeService::class)->cityIdIsInScope((int) $space->city_id, $user);
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
