<?php

namespace Modules\Ecommerce\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class EventService
{
    public function disabledDates($schedule, Carbon $minDate, Carbon $maxDate): array
    {
        $dates = [];

        $dateOverrides = $schedule
            ->dateOverrides()
            ->where('is_available', false)
            ->where(function ($query) use ($minDate, $maxDate) {
                $query
                    ->whereBetween('started_date', [
                        $minDate->copy()->subDay(),
                        $maxDate->copy()->addDay()
                    ])
                    ->orWhereBetween('ended_date', [
                        $minDate->copy()->subDay(),
                        $maxDate->copy()->addDay()
                    ]);
            })
            ->orderBy('started_date')
            ->get();

        foreach ($dateOverrides as $dateOverride) {
            if (! $dateOverride->ended_date) {
                $dates[] = $dateOverride->started_date->toDateString();
            } else {
                $startedDate = $dateOverrides->started_date->copy();
                $endedDate = $dateOverrides->ended_date->copy();

                for ($date = $startedDate; $date->lte($endedDate); $date->addDay()) {
                    $dates[] = $date->toDateString();
                }
            }
        }

        return $dates;
    }

    public function availableTimes($schedule, Carbon $date): array
    {
        $times = [];
        $dayOfWeek = $date->format('N');

        $weeklyHour = $schedule
            ->weeklyHours()
            ->where('day', $dayOfWeek)
            ->available()
            ->first();

        foreach ($weeklyHour->times as $scheduleTime) {
            $startedTime = Carbon::parse($scheduleTime->started_time);
            $endedTime = Carbon::parse($scheduleTime->ended_time);

            $product = $schedule->schedulable;
            $duration = $product->duration;
            $method = 'add'.Str::title(Str::plural($product->duration_unit));

            for ($i = $startedTime; $i->lte($endedTime); $i->$method($duration)) {
                $times[] = $i->format('H:i');
            }
        }

        return $times;
    }
}
