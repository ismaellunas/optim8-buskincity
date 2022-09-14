<?php

namespace Modules\Ecommerce\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Modules\Ecommerce\Services\EventService;

class AvailableBookingTime implements Rule, DataAwareRule
{
    private $data;
    private $schedule;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $availableTimes = app(EventService::class)->availableTimes(
            $this->schedule,
            Carbon::parse($this->data['date'])
        );

        return $availableTimes->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The :attribute is invalid.');
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
