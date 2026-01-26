<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Entities\ProductEvent;
use Modules\Booking\Http\Requests\ProductEventScheduleRequest;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;

class ProductEventScheduleController extends CrudController
{
    protected $title = 'Event schedule';

    public function __construct(
        private ProductEventService $productEventService
    ) {}

    public function edit(Product $product, ProductEvent $productEvent)
    {
        if ($productEvent->product_id !== $product->id) {
            abort(404);
        }

        $schedule = $productEvent->schedule;

        if (!$schedule) {
            $schedule = Schedule::factory()->state([
                'schedulable_type' => ProductEvent::class,
                'schedulable_id' => $productEvent->id,
                'timezone' => $productEvent->timezone ?? config('app.timezone'),
            ])->create();
        }

        return Inertia::render('Booking::ProductEventScheduleEdit', $this->getData([
            'product' => [
                'id' => $product->id,
                'name' => $product->displayName,
            ],
            'productEvent' => [
                'id' => $productEvent->id,
                'title' => $productEvent->title,
                'started_at' => $productEvent->started_at->toIso8601String(),
                'ended_at' => $productEvent->ended_at->toIso8601String(),
                'timezone' => $productEvent->timezone,
            ],
            'scheduleTimezone' => $schedule->timezone,
            'weekdays' => $this->productEventService->weekdays()->pluck('value', 'id'),
            'weeklyHours' => $this->productEventService->weeklyHoursForSchedule($schedule),
            'dateOverrides' => $this->productEventService->dateOverridesForSchedule($schedule),
            'i18n' => [
                'schedule' => __('Schedule'),
                'timezone' => __('Timezone'),
                'weekly_hours' => __('Weekly hours'),
                'date_override' => __('Date override'),
                'date_override_description' => __('Add dates when your availability changes from your weekly hours'),
                'add_date' => __('Add :resource', ['resource' => __('Date')]),
                'unavailable' => __('Unavailable'),
                'update' => __('Update'),
                'cancel' => __('Cancel'),
                'tips' => [
                    'timezone' => __('Select your timezone to ensure that all scheduled events and time-related information are accurate.'),
                    'weekly_hours' => __('Specify the available event hours that can be booked by performers on a weekly basis.'),
                    'date_override' => __('Use this field to manually select a specific date, overriding the weekly event hours.'),
                ],
            ],
        ]));
    }

    public function update(
        ProductEventScheduleRequest $request,
        Product $product,
        ProductEvent $productEvent
    ) {
        if ($productEvent->product_id !== $product->id) {
            abort(404);
        }

        $inputs = $request->validated();

        $schedule = $productEvent->schedule;

        if (!$schedule) {
            $schedule = Schedule::factory()->state([
                'schedulable_type' => ProductEvent::class,
                'schedulable_id' => $productEvent->id,
            ])->make();
        }

        $schedule->timezone = $inputs['timezone'];
        $schedule->save();

        $this->productEventService->saveWeeklyHours($inputs['weekly_hours'], $schedule);
        $this->productEventService->saveDateOverrides(collect($inputs['date_overrides']), $schedule);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title()
        ]);

        return Redirect::back();
    }
}
