<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Services\CityService;
use App\Services\SettingService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Http\Requests\ProductEventRequest;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;

class ProductEventController extends CrudController
{
    private $productEventService;
    private $cityService;

    protected $title = 'booking_module::terms.product booking_module::terms.event';

    public function __construct(ProductEventService $productEventService, CityService $cityService)
    {
        $this->productEventService = $productEventService;
        $this->cityService = $cityService;
    }

    /**
     * Override title method to properly translate concatenated translation keys
     */
    protected function title(): string
    {
        return \Illuminate\Support\Str::title(
            __('booking_module::terms.product') . ' ' . __('booking_module::terms.event')
        );
    }

    public function update(ProductEventRequest $request, Product $product)
    {
        $inputs = $request->all();

        $product->duration = $inputs['duration'];
        $product->bookable_date_range = $inputs['bookable_date_range'];
        $product->setMeta([
            'pitch_started_at' => $inputs['pitch_started_at'] ?? null,
            'pitch_ended_at' => $inputs['pitch_ended_at'] ?? null,
            'pitch_timezone' => $inputs['pitch_timezone'] ?? null,
        ]);

        $location = collect($inputs['location'])->only([
            'address',
            'city',
            'country_code',
        ])->all();

        $latitude = data_get($inputs, 'location.latitude', null);
        $longitude = data_get($inputs, 'location.longitude', null);

        $location['latitude'] = !is_null($latitude) ? (float) $latitude : null;
        $location['longitude'] = !is_null($longitude) ? (float) $longitude : null;

        if ($location['latitude'] && $location['longitude']) {
            $response = $this->getReversedGeocoding(
                $location['latitude'],
                $location['longitude']
            );

            if (
                $response->ok()
                && $response->json()['status'] != 'REQUEST_DENIED'
            ) {
                $location['geocode'] = $response->json()['results'][0];
            }
        }

        // Auto-create city in database if it doesn't exist (for data consistency)
        if (!empty($location['city']) && !empty($location['country_code'])) {
            try {
                $this->cityService->findOrCreate(
                    $location['city'],
                    $location['country_code'],
                    $location['latitude'],
                    $location['longitude']
                );
            } catch (\Exception $e) {
                // Log error but don't fail the request
                // Products can still work with city as text even if DB insert fails
                Log::warning("Failed to create city in database: {$e->getMessage()}");
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

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title()
        ]);

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
