<?php

namespace Modules\Ecommerce\Policies;

use App\Policies\BasePermissionPolicy;

class OrderPolicy extends BasePermissionPolicy
{
    protected $basePermission = 'order';
}
