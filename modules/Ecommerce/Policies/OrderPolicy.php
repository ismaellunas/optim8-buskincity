<?php

namespace Modules\Ecommerce\Policies;

use App\Policies\BasePermissionPolicy;
use Illuminate\Support\Traits\Macroable;

class OrderPolicy extends BasePermissionPolicy
{
    use Macroable;

    protected $basePermission = 'order';
}
