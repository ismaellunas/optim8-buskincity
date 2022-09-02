<?php

namespace Modules\Ecommerce\Enums;

enum BookingStatus: string
{
    case UPCOMING = 'upcoming';
    case ONGOING = 'ongoing';
    case PASSED = 'passed';
}
