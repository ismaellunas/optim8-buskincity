<?php

namespace Modules\Booking\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that two date fields span at most N inclusive calendar days.
 */
class MaxInclusiveDaySpan implements DataAwareRule, ValidationRule
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected array $data = [];

    public function __construct(
        private string $startField,
        private int $maxDays
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $startValue = data_get($this->data, $this->startField);

        if (blank($startValue) || blank($value)) {
            return;
        }

        $start = Carbon::parse($startValue)->startOfDay();
        $end = Carbon::parse($value)->startOfDay();

        if ($start->diffInDays($end) > ($this->maxDays - 1)) {
            $fail(__('The date range cannot exceed :days days.', ['days' => $this->maxDays]));
        }
    }
}
