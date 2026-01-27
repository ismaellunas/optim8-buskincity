<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use App\Policies\BasePermissionPolicy;
use App\Services\LoginService;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PublishingStatus;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\ProductStatus;
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
                || $user->hasRole('city_administrator')
            );
        }

        return true;
    }

    public function create(User $user)
    {
        return (
            parent::create($user)
            || $user->hasRole('city_administrator')
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
     * Check if a city administrator can manage a product through its linked space
     */
    private function canManageProductSpace(User $user, Model $product): bool
    {
        if (!$user->hasRole('city_administrator')) {
            return false;
        }

        // Check if product is linked to a Space
        if ($product->productable_type !== 'Modules\Space\Entities\Space' || !$product->productable_id) {
            return false;
        }

        $space = $product->productable;
        
        if (!$space) {
            return false;
        }

        // Check if user manages the space or the space is in their cities
        return $user->spaces->contains('id', $space->id) 
            || $user->adminCities->contains('id', $space->city_id);
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
            && $product->productEvents()
                ->where('status', PublishingStatus::PUBLISHED->value)
                ->exists()
        );
    }
}
