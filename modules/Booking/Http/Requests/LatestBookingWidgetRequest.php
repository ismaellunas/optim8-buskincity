<?php

namespace Modules\Booking\Http\Requests;

class LatestBookingWidgetRequest extends OrderIndexRequest
{
    public function authorize()
    {
        $user = auth()->user();
        return $user->can('order.browse')
            || $user->isProductManager();
    }
}
