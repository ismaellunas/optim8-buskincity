<?php

namespace App\Services;

use App\Models\City;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Resolves city/location IDs an actor may read or write (Phase 3 / T3.2).
 *
 * Global roles (Super Administrator, Administrator) return empty collections
 * meaning "no restriction" — callers treat empty + global as all allowed.
 */
class UserScopeService
{
    public function isGloballyScoped(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            config('permission.super_admin_role'),
            config('permission.role_names.admin'),
        ]);
    }

    /**
     * City ids the user may act within for scoped admin roles.
     *
     * @return array<int, int>
     */
    public function scopedCityIds(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return [];
        }

        $cityIds = [];

        if ($user->isCityAdministrator()) {
            $cityIds = array_merge(
                $cityIds,
                $user->scopeIdsFor(config('permission.role_names.city_admin'), 'city'),
                $user->adminCities()->pluck('cities.id')->map(fn ($id) => (int) $id)->all()
            );
        }

        if ($user->isSpecialEventsAdmin()) {
            $cityIds = array_merge(
                $cityIds,
                $user->scopeIdsFor(config('permission.role_names.special_events_admin'), 'city')
            );
        }

        return array_values(array_unique(array_map('intval', $cityIds)));
    }

    /**
     * @return array<int, int>
     */
    public function scopedLocationIds(?User $user = null): array
    {
        if ($this->isGloballyScoped($user)) {
            return [];
        }

        $cityIds = $this->scopedCityIds($user);

        if (empty($cityIds)) {
            return [];
        }

        return Location::whereIn('city_id', $cityIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function cityIdIsInScope(int $cityId, ?User $user = null): bool
    {
        if ($this->isGloballyScoped($user)) {
            return true;
        }

        return in_array($cityId, $this->scopedCityIds($user), true);
    }

    public function locationIdIsInScope(int $locationId, ?User $user = null): bool
    {
        if ($this->isGloballyScoped($user)) {
            return true;
        }

        $location = Location::find($locationId);

        if (! $location) {
            return false;
        }

        return $this->cityIdIsInScope((int) $location->city_id, $user);
    }

    /**
     * Assert scope or throw — used from controllers after resolving FKs.
     */
    public function assertCityInScope(int $cityId, ?User $user = null): void
    {
        if (! $this->cityIdIsInScope($cityId, $user)) {
            abort(403, __('You are not authorized to manage resources in this city.'));
        }
    }

    /**
     * Cities available for pickers for the current actor.
     */
    public function scopedCityOptions(?User $user = null): Collection
    {
        if ($this->isGloballyScoped($user)) {
            return City::orderBy('name')->get(['id', 'name', 'country_code', 'latitude', 'longitude']);
        }

        $cityIds = $this->scopedCityIds($user);

        if (empty($cityIds)) {
            return collect();
        }

        return City::whereIn('id', $cityIds)
            ->orderBy('name')
            ->get(['id', 'name', 'country_code', 'latitude', 'longitude']);
    }
}
