<?php

namespace Modules\Booking\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;

class BookingWithinPitchWindow implements ValidationRule
{
    public function __construct(private Product $product)
    {
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! app(ProductEventService::class)->isDateWithinPitchWindow($this->product, (string) $value)) {
            $fail(__('The selected date must be within the pitch bookable window.'));
        }
    }
}
