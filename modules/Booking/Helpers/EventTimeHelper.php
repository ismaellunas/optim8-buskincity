<?php

namespace Modules\Booking\Helpers;

use Illuminate\Support\Str;

class EventTimeHelper
{
    public static function calculateDurationMethodName(
        ?string $unit = null,
        string $operation = 'add'
    ): string {
        $unit = $unit ?? 'minute';

        return Str::lower($operation).Str::title(Str::plural($unit));
    }
}
