<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\FlashNotifiable;
use Illuminate\Http\Request;
use KMLaravel\GeographicalCalculator\Facade\GeoFacade;
use Modules\Booking\Entities\OrderCheckIn;
use Modules\Ecommerce\Entities\Order;

class CheckInController extends Controller
{
    use FlashNotifiable;

    public function __invoke(Request $request, Order $order)
    {
        $user = auth()->user();
        $product = $order->firstProduct;

        $checkInRadius = Setting::key('check_in_radius')->value('value');

        $baseLocation = collect($product->locations[0])->only(['latitude', 'longitude']);
        $location = collect($request->get('geolocation'))->only(['latitude', 'longitude']);

        $distance = app(GeoFacade::class)
            ->setPoint([
                $baseLocation['latitude'],
                $baseLocation['longitude']
            ])
            ->setPoint([
                $location['latitude'],
                $location['longitude']
            ])
            ->setOptions(['units' => ['m']])
            ->getDistance()["1-2"]["m"];

        $radius = $checkInRadius['value'];
        if ($checkInRadius['unit'] == 'km') {
            $radius = $checkInRadius['value'] * 1000;
        }

        $isAllowed = $distance <= $radius;

        OrderCheckIn::factory()
            ->state([
                'geolocation' => $location->toArray(),
                'data' => [
                    'base_geolocation' => $baseLocation->toArray(),
                ],
                'is_allowed' => $isAllowed,
                'user_id' => $user->id,
                'order_id' => $order->id,
            ])
            ->create();

        if ($isAllowed) {
            $this->generateFlashMessage("Thanks for checking-in for this booking");
        } else {
            $this->generateFlashMessage("We couldn't allow your check-in. Please move closer to the booking location");
        }

        return back()->with('is_allowed', $isAllowed);
    }
}
