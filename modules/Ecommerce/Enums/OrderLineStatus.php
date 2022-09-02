<?php

namespace Modules\Ecommerce\Enums;

enum OrderLineStatus: string
{
    case PHYSICAL = 'physical';
    case DIGITAL = 'digital';
    case EVENT = 'event';
}
