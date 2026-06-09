<?php

namespace Modules\Booking\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;

class BookingWithinPitchWindow implements InvokableRule
{
    public function __construct(private Product $product)
    {
    }

    public function __invoke($attribute, $value, $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (! app(ProductEventService::class)->isDateWithinPitchWindow($this->product, (string) $value)) {
            $fail(__('The selected date must be within the pitch bookable window.'));
        }
    }
}
