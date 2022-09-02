<?php

namespace Modules\Ecommerce\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
}
