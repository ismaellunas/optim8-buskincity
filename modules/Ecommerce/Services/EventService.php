<?php

namespace Modules\Ecommerce\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Ecommerce\Entities\Schedule;
use Modules\Ecommerce\Entities\ScheduleRule;
use Modules\Ecommerce\Enums\BookingStatus;
use Modules\Ecommerce\Helpers\EventTimeHelper;

class EventService
{
    public function disabledDates($schedule, Carbon $minDate, Carbon $maxDate): Collection
    {
        $dates = collect();

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
                $dates->push($dateOverride->started_date->toDateString());
            } else {
                $startedDate = $dateOverrides->started_date->copy();
                $endedDate = $dateOverrides->ended_date->copy();

                for ($date = $startedDate; $date->lte($endedDate); $date->addDay()) {
                    $dates->push($date->toDateString());
                }
            }
        }

        $todayDateString = today()->toDateString();

        if (!$dates->contains($todayDateString)) {
            $dates->push($todayDateString);
        }

        return $dates;
    }

    private function bookedTimes(Schedule $schedule, Carbon $date): Collection
    {
        return $schedule
            ->events()
            ->where('status', BookingStatus::UPCOMING)
            ->whereDate('booked_at', $date->toDateString())
            ->get([
                'id',
                'booked_at',
                'schedule_id',
                'duration_unit',
                'duration'
            ])
            ->map(function ($event) {
                $method = EventTimeHelper::calculateDurationMethodName($event->duration_unit);

                $startTime = Carbon::parse(
                    $event->booked_at->format('H:i')
                );

                $endTime = Carbon::parse($event->booked_at->format('H:i'));
                $endTime->$method($event->duration);

                return (object) [
                    'bookedAt' => $event->booked_at,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                ];
            });
    }

    public function getScheduleRuleOnDate($schedule, Carbon $date): ?ScheduleRule
    {
        $dateOverrides = $schedule
            ->dateOverrides()
            ->where(function ($query) use ($date) {
                $query
                    ->where(function ($query) use ($date) {
                        $query
                            ->whereDate('started_date', '=', $date->toDateString());
                    })
                    ->orWhere(function ($query) use ($date) {
                        $query
                            ->whereDate('started_date', '<', $date->toDateString())
                            ->whereDate('ended_date', '>=', $date->toDateString());
                    });
            })
            ->get();

        if ($dateOverrides->where('is_available', false)->isNotEmpty()) {
            return null;
        }

        $availableDateOverrides = $dateOverrides->where('is_available', true);

        if ($availableDateOverrides->isNotEmpty()) {
            return $availableDateOverrides->last();
        }

        $dayOfWeek = $date->format('N');

        return $schedule
            ->weeklyHours()
            ->where('day', $dayOfWeek)
            ->available()
            ->first();
    }

    public function availableTimes(Schedule $schedule, Carbon $date): Collection
    {
        $times = collect();

        $rule = $this->getScheduleRuleOnDate($schedule, $date);

        if (!$rule) {
            return $times;
        }

        $bookedTimes = $this->bookedTimes($schedule, $date);

        foreach ($rule->times as $scheduleTime) {
            $startedTime = Carbon::parse($scheduleTime->started_time);
            $endedTime = Carbon::parse($scheduleTime->ended_time);

            $product = $schedule->schedulable;
            $duration = $product->duration;
            $method = EventTimeHelper::calculateDurationMethodName($product->duration_unit);

            for ($i = $startedTime; $i->lte($endedTime); $i->$method($duration)) {
                $currentEndTime = $i->copy();
                $currentEndTime->$method($duration);

                $isTimeBooked = $bookedTimes->contains(function ($booked) use ($i, $currentEndTime) {
                    return (
                        $i->eq($booked->startTime)
                        || (
                            $i->gte($booked->startTime)
                            && $i->isBefore($booked->endTime)
                        )
                        || (
                            $currentEndTime->gt($booked->startTime)
                            && $currentEndTime->lt($booked->endTime)
                        )
                    );
                });

                if (
                    !$isTimeBooked
                    && $currentEndTime->lte($endedTime)
                ) {
                    $times->push($i->format('H:i'));
                }
            }
        }

        return $times;
    }
}
