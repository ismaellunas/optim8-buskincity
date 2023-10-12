<?php

namespace App\Events;

use App\Models\Module;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class ModuleDeactivated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Module $module)
    {}
}
