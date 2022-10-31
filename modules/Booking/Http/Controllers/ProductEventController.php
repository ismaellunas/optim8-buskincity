<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use Modules\Booking\Http\Requests\ProductEventRequest;
use Modules\Ecommerce\Entities\Product;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Services\ProductEventService;

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
        $product->locations = [$inputs['location']];
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
}
