<?php

namespace Modules\Ecommerce\Enums;

enum OrderLineType: string
{
    case PHYSICAL = 'physical';
    case DIGITAL = 'digital';
    case EVENT = 'event';
}
