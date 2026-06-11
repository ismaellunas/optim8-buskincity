<?php

namespace Modules\Booking\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Modules\Booking\Services\EventService;

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
        $date = $this->resolveDateForAttribute($attribute);

        if (! $date) {
            return false;
        }

        $availableTimes = $this->service->availableTimes(
            $this->schedule,
            $date
        );

        return $availableTimes->contains($value);
    }

    private function resolveDateForAttribute(string $attribute): ?string
    {
        if (! empty($this->data['date'])) {
            return $this->data['date'];
        }

        if (preg_match('/^slots\.(\d+)\.time$/', $attribute, $matches)) {
            return $this->data['slots'][$matches[1]]['date'] ?? null;
        }

        return null;
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
