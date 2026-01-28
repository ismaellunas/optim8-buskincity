<?php

namespace App\Policies;

use App\Enums\PublishingStatus;
use App\Models\User;
use App\Services\LoginService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Booking\Entities\ProductEvent;
use Modules\Ecommerce\Enums\ProductStatus;

class ProductEventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->can('product.browse')
            || $user->can('product.edit')
            || $user->hasRole('city_administrator');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductEvent $productEvent)
    {
        return $user->can('update', $productEvent->product);
    }

    public function showFrontendProductEvent(?User $user, ProductEvent $productEvent): bool
    {
        if (!$user) {
            return false;
        }

        $product = $productEvent->product;

        return LoginService::isUserHomeUrl()
            && $productEvent->status === PublishingStatus::PUBLISHED->value
            && $product
            && $product->status === ProductStatus::PUBLISHED->value
            && $user->hasRole($product->roles);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->can('product.add')
            || $user->hasRole('city_administrator');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductEvent $productEvent)
    {
        return $user->can('update', $productEvent->product);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductEvent $productEvent)
    {
        return $user->can('delete', $productEvent->product)
            || $user->can('update', $productEvent->product);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductEvent $productEvent)
    {
        return $user->can('update', $productEvent->product);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductEvent $productEvent)
    {
        return $user->can('delete', $productEvent->product);
    }
}
