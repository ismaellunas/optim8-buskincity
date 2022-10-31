<?php

namespace Modules\Booking\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use App\Models\Setting;
use Modules\Ecommerce\Entities\Order;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;

class CheckInPosition implements Rule, DataAwareRule
{
    private $data;
    private $order;
    private $latitudeField;
    private $longitudeField;

    public function __construct(Order $order, string $latitudeField, string $longitudeField)
    {
        $this->order = $order;
        $this->latitudeField = $latitudeField;
        $this->longitudeField = $longitudeField;
    }

    public function passes($attribute, $value): bool
    {
        $product = $this->order->firstProduct;

        $checkInRadius = json_decode(Setting::key('check_in_radius')->value('value'));

        $productLocation = collect($product->locations[0])->only(['latitude', 'longitude']);

        $distance = GeoFacade::
            setPoint([
                $productLocation['latitude'],
                $productLocation['longitude']
            ])
            ->setPoint([
                Arr::get($this->data, $this->latitudeField),
                Arr::get($this->data, $this->longitudeField)
            ])
            ->setOptions(['units' => ['m']])
            ->getDistance()["1-2"]["m"];

        $radius = $checkInRadius->value;

        if ($checkInRadius->unit == 'km') {
            $radius = $checkInRadius->value * 1000;
        }

        return ($distance <= $radius);
    }

    public function message(): string
    {
        return __("We couldn't allow your check-in.<br>Please move closer to the booking location.");
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
