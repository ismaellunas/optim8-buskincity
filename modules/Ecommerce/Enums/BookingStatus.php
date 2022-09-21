<?php

namespace Modules\Ecommerce\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum BookingStatus: string
{
    case UPCOMING = 'upcoming';
    case ONGOING = 'ongoing';
    case PASSED = 'passed';
    case CANCELED = 'canceled';
    case RESCHEDULED = 'rescheduled';

    public static function options(): Collection
    {
        return collect(self::cases())
            ->map(fn ($option) => [
                'id' => $option->value, 'value' => Str::title($option->value)
            ]);
    }
}
