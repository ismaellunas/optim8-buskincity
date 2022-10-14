<?php

namespace Modules\Booking\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class NoOverlappingTime implements Rule, DataAwareRule
{
    private $data = [];
    private $startTime;
    private $endTime;

    private function times($attribute): array
    {
        $indexes = explode('.', $attribute);

        return $this->data[$indexes[0]][$indexes[1]][$indexes[2]];
    }

    private function computedStartEndTime($time): string
    {
        return $time['started_time'].'-'.$time['ended_time'];
    }

    private function isOverlappingWithPrevious(array $prevTime): bool
    {
        $prevStartTime = Carbon::createFromTimeString($prevTime['started_time']);
        $prevEndTime = Carbon::createFromTimeString($prevTime['ended_time']);

        return (
            $this->startTime->isBefore($prevEndTime)
            || $this->startTime->equalTo($prevStartTime)
            || $this->endTime->lessThanOrEqualTo($prevEndTime)
        );
    }

    private function isOverlappingWithNext(array $nextTime): bool
    {
        $nextStartTime = Carbon::createFromTimeString($nextTime['started_time']);
        $nextEndTime = Carbon::createFromTimeString($nextTime['ended_time']);

        return (
            $this->endTime->isAfter($nextStartTime)
            || $this->endTime->greaterThanOrEqualTo($nextEndTime)
            || $this->startTime->equalTo($nextStartTime)
        );
    }

    public function passes($attribute, $value): bool
    {
        $times = collect($this->times($attribute));

        $this->startTime = Carbon::createFromTimeString($value['started_time']);
        $this->endTime = Carbon::createFromTimeString($value['ended_time']);

        $mappedTimes = $times
            ->map(function ($time) {
                return [
                    'computed_startend_time' => $this->computedStartEndTime($time),
                    'started_time' => $time['started_time'],
                    'ended_time' => $time['ended_time'],
                ];
            })
            ->sortBy('computed_startend_time')
            ->values();

        $key = $mappedTimes
            ->search(function ($time) use ($value) {
                return $time['computed_startend_time'] == $this->computedStartEndTime($value);
            });

        $isOverlapping = false;

        if ($mappedTimes->has($key - 1)) {
            $isOverlapping = $this
                ->isOverlappingWithPrevious((array) $mappedTimes[ $key - 1 ]);
        }

        if (!$isOverlapping && $mappedTimes->has($key + 1)) {
            $isOverlapping = $this
                ->isOverlappingWithNext((array) $mappedTimes[ $key + 1 ]);
        }

        return !$isOverlapping;
    }

    public function message(): string
    {
        return __('Times overlap with another set of times.');
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
