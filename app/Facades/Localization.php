<?php

namespace App\Facades;

use App\Entities\Localization as LaravelLocalization;
use Illuminate\Support\Facades\Facade;

class Localization extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LaravelLocalization::class;
    }
}
