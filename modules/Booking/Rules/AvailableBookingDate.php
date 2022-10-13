<?php

namespace Modules\Booking\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Modules\Ecommerce\Services\EventService;

class AvailableBookingDate implements Rule
{
    private $schedule;
    private $service;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($schedule)
    {
        $this->schedule = $schedule;
        $this->service = app(EventService::class);
    }

    public function passes($attribute, $value): bool
    {
        $allowedDates = $this->service->allowedDatesBetween(
            $this->schedule,
            Carbon::parse($value),
            Carbon::parse($value)
        );

        return $allowedDates->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('The :attribute is invalid.');
    }
}
