<?php

namespace Modules\Booking\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Modules\Ecommerce\Services\EventService;

class AvailableBookingTime implements Rule, DataAwareRule
{
    private $data;
    private $schedule;
    private $service;

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
        $this->service = app(EventService::class);
    }

    public function passes($attribute, $value): bool
    {
        $availableTimes = $this->service->availableTimes(
            $this->schedule,
            $this->data['date']
        );

        return $availableTimes->contains($value);
    }

    public function message(): string
    {
        return __('The :attribute is invalid.');
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
