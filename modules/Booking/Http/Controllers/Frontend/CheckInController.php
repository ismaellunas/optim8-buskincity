<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\FlashNotifiable;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;
use Modules\Booking\Entities\OrderCheckIn;
use Modules\Booking\Http\Requests\CheckInRequest;
use Modules\Ecommerce\Entities\Order;

class CheckInController extends Controller
{
    use FlashNotifiable;

    public function __invoke(CheckInRequest $request, Order $order)
    {
        $product = $order->firstProduct;

        $baseLocation = collect($product->locations[0])->only(['latitude', 'longitude']);

        $location = collect($request->get('geolocation'))->only(['latitude', 'longitude']);

        $checkInRadius = json_decode(Setting::key('check_in_radius')->value('value'));

        $distance = GeoFacade::
            setPoint([
                $baseLocation['latitude'],
                $baseLocation['longitude']
            ])
            ->setPoint([
                $location['latitude'],
                $location['longitude']
            ])
            ->setOptions(['units' => ['m']])
            ->getDistance()["1-2"]["m"];

        $radius = $checkInRadius->value;

        if ($checkInRadius->unit == 'km') {
            $radius = $checkInRadius->value * 1000;
        }

        OrderCheckIn::factory()
            ->state([
                'geolocation' => $location->toArray(),
                'data' => [
                    'base_geolocation' => $baseLocation->toArray(),
                    'calculated_distance_in_meters' => $distance,
                    'allowed_distance_in_meters' => $radius,
                ],
                'is_allowed' => true,
                'user_id' => auth()->user()->id,
                'order_id' => $order->id,
            ])
            ->create();

        $this->generateFlashMessage("Thanks for checking-in for this booking");
    }
}
