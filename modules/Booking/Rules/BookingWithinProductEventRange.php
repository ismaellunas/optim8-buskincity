<?php

namespace Modules\Booking\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Modules\Booking\Entities\ProductEvent;

class BookingWithinProductEventRange implements Rule
{
    public function __construct(private ProductEvent $productEvent)
    {
    }

    public function passes($attribute, $value)
    {
        $date = request()->get('date');
        $time = request()->get('time');

        if (empty($date) || empty($time)) {
            return false;
        }

        if (!$this->productEvent->started_at || !$this->productEvent->ended_at) {
            return false;
        }

        $requested = Carbon::parse(
            $date.' '.$time,
            $this->productEvent->timezone ?? config('app.timezone')
        );

        return $requested->between(
            $this->productEvent->started_at,
            $this->productEvent->ended_at
        );
    }

    public function message()
    {
        return __('The selected date and time must be within the event range.');
    }
}
