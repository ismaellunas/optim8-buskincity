<?php

namespace Modules\Booking\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum CheckInType: string
{
    case DISTANCE = 'distance';
    case TIME = 'time';

    public static function options(): Collection
    {
        return collect(self::cases())
            ->map(fn ($option) => [
                'id' => $option->value, 'value' => Str::title($option->value)
            ]);
    }
}
