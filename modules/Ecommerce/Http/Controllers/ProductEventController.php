<?php

namespace Modules\Ecommerce\Http\Controllers;

//use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\Schedule;
use Modules\Ecommerce\Http\Requests\ProductEventRequest;
use Modules\Ecommerce\Services\ProductEventService;

class ProductEventController extends CrudController
{
    private $productEventService;

    public function __construct(ProductEventService $productEventService)
    {
        $this->productEventService = $productEventService;
    }

    protected $title = 'Product Event';

    public function update(ProductEventRequest $request, Product $product)
    {
        $inputs = $request->all();

        $product->duration = $inputs['duration'];
        $product->bookable_data_range = $inputs['bookable_date_range'];
        $product->save();

        $schedule = $product->eventSchedule ?? Schedule::factory()->state([
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ])->make();

        $schedule->timezone = $inputs['timezone'];
        $schedule->save();

        $this->productEventService->saveWeeklyHours($inputs['weekly_hours'], $schedule);

        $this->productEventService->saveDateOverrides(collect($inputs['date_overrides']), $schedule);

        /*
        $dateOverrideRules = $scheduleRules
            ->filter(fn ($rule) => $rule->type == ScheduleRule::TYPE_DATE_OVERRIDE);

        $inputRules = collect($inputs['date_overrides']);

        $unusedDateOverrideIds = $dateOverrideRules->pluck('id')->diff(
            $inputRules->pluck('id')->all()
        );

        if (!empty($unusedDateOverrideIds)) {
            $unusedDateOverrides = $dateOverrideRules->whereIn('id', $unusedDateOverrideIds);

            foreach ($unusedDateOverrides as $unusedDateOverride) {
                $unusedDateOverride->delete();
            }
        }

        foreach ($inputRules as $inputRule) {
            if (!empty($inputRule['id'])) {
                $dateOverrideRule = $dateOverrideRules
                    ->first(fn ($rule) => $rule->id == $inputRule['id']);

                if (is_null($dateOverrideRule)) {
                    continue;
                }

                $dateOverrideRule->started_date = $inputRule['started_date'];
                $dateOverrideRule->ended_date = $inputRule['ended_date'];
                $dateOverrideRule->is_available = !empty($inputRule['times']);
                $dateOverrideRule->save();

            } else {
                $dateOverrideRule = ScheduleRule::factory()
                    ->state([
                        'schedule_id' => $schedule->id,
                        'started_date' => $inputRule['started_date'],
                        'ended_date' => $inputRule['ended_date'],
                        'type' => ScheduleRule::TYPE_DATE_OVERRIDE,
                        'is_available' => !empty($inputRule['times']),
                    ])
                    ->create();

            }

            $scheduleRuleTimes = $dateOverrideRule->times;

            $inputTimes = collect($inputRule['times']);

            $unusedTimeIds = $scheduleRuleTimes->pluck('id')->diff(
                $inputTimes->pluck('id')->all()
            );

            if (!empty($unusedTimeIds)) {
                $unusedTimes = $scheduleRuleTimes->whereIn('id', $unusedTimeIds);

                foreach ($unusedTimes as $unusedTime) {
                    $unusedTime->delete();
                }
            }

            foreach ($inputTimes as $inputTime) {

                if (!empty($inputTime['id'])) {
                    $scheduleRuleTime = $scheduleRuleTimes
                        ->first(fn ($time) => $time->id == $inputTime['id']);

                    if (is_null($scheduleRuleTime)) {
                        continue;
                    }

                    $scheduleRuleTime->started_time = $inputTime['started_time'];
                    $scheduleRuleTime->ended_time = $inputTime['ended_time'];

                } else {
                    $scheduleRuleTime = ScheduleRuleTime::factory()
                        ->state([
                            'schedule_rule_id' => $dateOverrideRule->id,
                            'started_time' => $inputTime['started_time'],
                            'ended_time' => $inputTime['ended_time'],
                        ])
                        ->make();
                }

                $scheduleRuleTime->save();
            }
        }
        */

        $this->generateFlashMessage('Successfully updating '.$this->title.'!');

        return back();
    }
}
