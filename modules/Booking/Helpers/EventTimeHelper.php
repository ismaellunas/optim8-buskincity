<?php

namespace Modules\Booking\Helpers;

use Illuminate\Support\Str;

class EventTimeHelper
{
    public static function calculateDurationMethodName(
        string $unit,
        string $operation = 'add'
    ): string {
        return Str::lower($operation).Str::title(Str::plural($unit));
    }
}
