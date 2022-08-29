<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;

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
            || $user->managedSpaceProducts->isNotEmpty()
        );
    }

    public function create(User $user)
    {
        return (
            $user->can('product.add')
            || $user->managedSpaceProducts->isNotEmpty()
        );
    }

    public function update(User $user, Product $product)
    {
        return (
            $user->can('product.edit')
            || $this->manageProductFromSpace($user, $product)
        );
    }

    public function delete(User $user, Product $product)
    {
        return (
            $user->can('product.delete')
            || $this->manageProductFromSpace($user, $product)
        );
    }

    public function manageProductFromSpace(User $user, Product $product)
    {
        return (
            !is_null($product->productable)
            && is_a($product->productable, Space::class)
            && $user->managedSpaceProducts->contains($product->productable)
        );
    }
}
