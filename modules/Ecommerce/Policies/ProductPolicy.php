<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use App\Policies\BasePermissionPolicy;
use App\Services\LoginService;
use Illuminate\Database\Eloquent\Model;
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
            );
        }

        return true;
    }

    public function update(User $user, Model $product)
    {
        return (
            parent::update($user, $product)
            || $user->products->contains($product)
        );
    }

    public function delete(User $user, Model $product)
    {
        return (
            parent::delete($user, $product)
            || $user->products->contains($product)
        );
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
        );
    }
}
