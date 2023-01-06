<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Http\Requests\ProductEventRequest;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;

class ProductEventController extends CrudController
{
    private $productEventService;

    protected $title = 'Product Event';

    public function __construct(ProductEventService $productEventService)
    {
        $this->productEventService = $productEventService;
    }

    public function update(ProductEventRequest $request, Product $product)
    {
        $inputs = $request->all();

        $product->duration = $inputs['duration'];
        $product->bookable_date_range = $inputs['bookable_date_range'];

        $location = $inputs['location'];

        if ($location['latitude'] && $location['longitude']) {
            $response = $this->getReversedGeocoding(
                $location['latitude'],
                $location['longitude']
            );

            if ($response->ok()) {
                $location['geocode'] = $response->json()['results'][0];
            }
        }

        $product->locations = [$location];
        $product->save();

        $schedule = $product->eventSchedule ?? Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->make();

        $schedule->timezone = $inputs['timezone'];
        $schedule->save();

        $this->productEventService->saveWeeklyHours($inputs['weekly_hours'], $schedule);

        $this->productEventService->saveDateOverrides(collect($inputs['date_overrides']), $schedule);

        $this->generateFlashMessage('Successfully updating '.$this->title.'!');

        return back();
    }

    private function getReversedGeocoding($latitude, $longitude): Response
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        $key = app(SettingService::class)->getGoogleApi();

        return Http::get($url, [
            'latlng' => $latitude.','.$longitude,
            'key' => $key,
        ]);
    }
}
