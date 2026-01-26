<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Booking\Entities\ProductEvent;

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
