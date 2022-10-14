<?php

namespace Modules\Booking\Database\factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Booking\Entities\Schedule;

class ScheduleRuleFactory extends Factory
{
    protected $model = \Modules\Booking\Entities\ScheduleRule::class;

    public function definition()
    {
        return [
            'type' => $this->model::TYPE_WEEKLY_HOUR,
            'schedule_id' => Schedule::factory(),
        ];
    }

    public function weeklyHourType()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => $this->model::TYPE_WEEKLY_HOUR,
            ];
        });
    }

    public function dateOverrideType()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => $this->model::TYPE_DATE_OVERRIDE,
            ];
        });
    }

    public function dayOfWeek(int|string $day)
    {
        if (!is_numeric($day)) {
            $day = date('N', strtotime('Monday'));
        }

        return $this->state(function (array $attributes) use ($day) {
            return [
                'day' => $day,
            ];
        });
    }

    public function available($availability = true)
    {
        return $this->state(function (array $attributes) use ($availability) {
            return [
                'is_available' => $availability,
            ];
        });
    }

    public function dateRange(Carbon $startedDate, Carbon $endedDate = null)
    {
        return $this->state(function (array $attributes) use ($startedDate, $endedDate) {
            $dateRanges = [];
            $dateRanges['started_date'] = $startedDate->toDateString();

            if (!is_null($endedDate)) {
                $dateRanges['ended_date'] = $endedDate->toDateString();
            }

            return $dateRanges;
        });
    }
}
