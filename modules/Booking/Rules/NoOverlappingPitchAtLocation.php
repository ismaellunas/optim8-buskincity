<?php

namespace Modules\Booking\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Modules\Booking\Services\PitchBookingService;
use Modules\Ecommerce\Entities\Product;

/**
 * OQ5: forbid overlapping pitch date ranges at the same saved location (space).
 */
class NoOverlappingPitchAtLocation implements DataAwareRule, Rule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    public function __construct(private ?Product $exceptProduct = null)
    {
    }

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
        if (blank($value)) {
            return true;
        }

        $start = data_get($this->data, 'pitch_started_at');
        $end = data_get($this->data, 'pitch_ended_at');

        if (blank($start) || blank($end)) {
            return true;
        }

        return app(PitchBookingService::class)->overlappingPitchAtSpace(
            (int) $value,
            (string) $start,
            (string) $end,
            $this->exceptProduct?->id
        ) === null;
    }

    public function message(): string
    {
        return __('Another pitch at this location already covers part of the selected date range. Choose different dates or a different location.');
    }
}
