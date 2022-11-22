<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use App\Policies\BasePermissionPolicy;
use App\Services\ModuleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;

class OrderPolicy extends BasePermissionPolicy
{
    use Macroable;

    private bool $isModuleSpaceActive;

    protected $basePermission = 'order';

    public function __construct()
    {
        $this->isEcommerceModuleActive = app(ModuleService::class)->isModuleActive('ecommerce');
    }

    public function viewAny(User $user)
    {
        if (!$this->isEcommerceModuleActive) {
            return false;
        }

        return (
            parent::viewAny($user)
            || auth()->check()
        );
    }

    public function view(User $user, Model $order)
    {
        if (!$this->isEcommerceModuleActive) {
            return false;
        }

        return (
            parent::view($user, $order)
            || $order->isPlacedByUser($user)
        );
    }
}
