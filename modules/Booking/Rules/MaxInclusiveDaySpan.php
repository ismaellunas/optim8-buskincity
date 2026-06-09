<?php

namespace Modules\Booking\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validates that two date fields span at most N inclusive calendar days.
 */
class MaxInclusiveDaySpan implements DataAwareRule, Rule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    public function __construct(
        private string $startField,
        private int $maxDays
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function passes($attribute, $value): bool
    {
        $startValue = data_get($this->data, $this->startField);

        if (blank($startValue) || blank($value)) {
            return true;
        }

        $start = Carbon::parse($startValue)->startOfDay();
        $end = Carbon::parse($value)->startOfDay();

        return $start->diffInDays($end) <= ($this->maxDays - 1);
    }

    public function message(): string
    {
        return __('The date range cannot exceed :days days.', ['days' => $this->maxDays]);
    }
}
