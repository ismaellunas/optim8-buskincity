<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use App\Policies\BasePermissionPolicy;
use App\Services\LoginService;
use App\Services\UserScopeService;
use Illuminate\Database\Eloquent\Model;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Space\Entities\Space;
use Illuminate\Support\Traits\Macroable;

class ProductPolicy extends BasePermissionPolicy
{
    use Macroable;

    protected $basePermission = 'product';

    public function viewAny(User $user)
    {
        if (LoginService::isAdminHomeUrl()) {
            return (
                $user->can('product.browse')
                || $user->isProductManager()
                || $user->isCityAdministrator()
                || $user->isSpecialEventsAdmin()
            );
        }

        return true;
    }

    public function create(User $user)
    {
        return (
            parent::create($user)
            || $user->isCityAdministrator()
            || $user->isSpecialEventsAdmin()
        );
    }

    public function update(User $user, Model $product)
    {
        return (
            parent::update($user, $product)
            || $user->products->contains($product)
            || $this->canManageProductSpace($user, $product)
        );
    }

    public function delete(User $user, Model $product)
    {
        return (
            parent::delete($user, $product)
            || $user->products->contains($product)
            || $this->canManageProductSpace($user, $product)
        );
    }

    /**
     * Scoped admins may manage pitches they own or that belong to their cities.
     * Special Events Admins are limited to special-event pitches (OQ1).
     */
    private function canManageProductSpace(User $user, Model $product): bool
    {
        if ($user->isSpecialEventsAdmin()) {
            if (! $product->is_special_event) {
                return false;
            }

            return $this->productIsInScopedCities($user, $product);
        }

        if (! $user->isCityAdministrator()) {
            return false;
        }

        return $this->productIsInScopedCities($user, $product);
    }

    private function productIsInScopedCities(User $user, Model $product): bool
    {
        if ($user->products->contains($product)) {
            return true;
        }

        $scopeService = app(UserScopeService::class);

        if ($product->city_id && $scopeService->cityIdIsInScope((int) $product->city_id, $user)) {
            return true;
        }

        if ($product->productable_type !== Space::class || ! $product->productable_id) {
            return false;
        }

        $space = $product->productable;

        if (! $space) {
            return false;
        }

        if ($user->spaces->contains('id', $space->id)) {
            return true;
        }

        return $space->city_id
            && $scopeService->cityIdIsInScope((int) $space->city_id, $user);
    }

    public function manageManager(User $user)
    {
        return ($user->isAdministrator || $user->isSuperAdministrator);
    }

    public function showFrontendProductEvent(User $user, Product $product)
    {
        return (
            LoginService::isUserHomeUrl()
            && $product->status == ProductStatus::PUBLISHED->value
            && $user->hasRole($product->roles)
            && $product->eventSchedule
        );
    }
}
