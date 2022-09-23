<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Ecommerce\Entities\Product;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return (
            $user->can('product.browse')
            || $user->products->isNotEmpty()
        );
    }

    public function create(User $user)
    {
        return (
            $user->can('product.add')
        );
    }

    public function update(User $user, Product $product)
    {
        return (
            $user->can('product.edit')
            || $user->products->contains($product)
        );
    }

    public function delete(User $user, Product $product)
    {
        return (
            $user->can('product.delete')
            || $user->products->contains($product)
        );
    }

    public function manageManager(User $user)
    {
        return ($user->isAdministrator || $user->isSuperAdministrator);
    }
}
