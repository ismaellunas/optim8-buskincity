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
        // $attribute is like 'slots.0.date'
        // $value is the date being validated (e.g., '2026-02-05')
        
        // Extract the slot index from the attribute path
        // e.g., 'slots.0.date' -> get time from 'slots.0.time'
        $attributePath = str_replace('.date', '', $attribute);
        $timeAttribute = $attributePath . '.time';
        
        // Get the time from the same slot
        $slotData = data_get(request()->all(), $attributePath);
        $time = $slotData['time'] ?? null;

        if (empty($value) || empty($time)) {
            return false;
        }

        if (!$this->productEvent->started_at || !$this->productEvent->ended_at) {
            return false;
        }

        $requested = Carbon::parse(
            $value.' '.$time,
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
