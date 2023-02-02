<?php

namespace Modules\Booking\Http\Requests;

class LatestBookingWidgetRequest extends OrderIndexRequest
{
    public function authorize()
    {
        return auth()->user()->can('order.browse');
    }
}
