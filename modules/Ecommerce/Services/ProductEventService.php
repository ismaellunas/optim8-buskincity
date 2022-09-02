<?php

namespace Modules\Ecommerce\Services;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\Schedule;
use Modules\Ecommerce\Entities\ScheduleRule;
use Modules\Ecommerce\Entities\ScheduleRuleTime;

class ProductEventService
{
    private $timezones;

    public function durationOptions(): Collection
    {
        return collect([
            ['id' => '15', 'value' => '15'],
            ['id' => '30', 'value' => '30'],
            ['id' => '45', 'value' => '45'],
            ['id' => '60', 'value' => '60'],
            ['id' => '90', 'value' => '90'],
            ['id' => '120', 'value' => '120'],
            ['id' => '180', 'value' => '180'],
            ['id' => '240', 'value' => '240'],
        ]);
    }

    public function startTimeIncrementOptions(): Collection
    {
        return collect([]);
    }

    public function formResource(Product $product): array
    {
        return [
            'duration' => $product->duration,
            'bookable_date_range_type' => $product->bookable_data_range_type,
            'bookable_date_range' => $product->bookable_data_range,
            'location' => null,
            'timezone' => $product->eventSchedule->timezone ?? null,
        ];
    }

    public function bookableDateRangeTypeOptions(): Collection
    {
        return collect([
            ['id' => 'calendar_days_into_the_future', 'value' => 'Calendar days into the future'],
            ['id' => 'week_days_into_the_future', 'value' => 'Week days into the future'],
            ['id' => 'date_range', 'value' => 'Date Range'],
            ['id' => 'indefinitely_into_the_future', 'value' => 'Indefinitely into the future'],
        ]);
    }

    public function timezoneOptions(): Collection
    {
        if (! is_null($this->timezones)) {
            return $this->timezones;
        }

        $timezones = array();
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( DateTimeZone::ALL ) );

        $timezoneOffsets = array();
        foreach ($timezones as $timezone) {
            $tz = new DateTimeZone($timezone);
            $timezoneOffsets[$timezone] = $tz->getOffset(new DateTime());
        }

        asort($timezoneOffsets);

        $timezoneList = [];
        foreach ($timezoneOffsets as $timezone => $offset) {
            $offsetPrefix = $offset < 0 ? '-' : '+';
            $offsetFormatted = gmdate( 'H:i', abs($offset) );

            $prettyOffset = "UTC${offsetPrefix}${offsetFormatted}";

            $timezoneList[] = [
                'id' => $timezone,
                'value' => "(${prettyOffset}) $timezone",
            ];
        }

        $this->timezones = collect($timezoneList);

        return $this->timezones;
    }

    public function weeklyHours(Product $product): array
    {
        $weeklyHours = [];
        $rules = $product->eventSchedule->weeklyHours ?? null;

        for ($day = 1; $day <= 7; $day++) {

            $rule = null;
            if (!is_null($rules)) {
                $rule = $rules->first(fn ($rule) => $rule->day == $day);
            }

            $ruleData = [];

            if ($rule) {
                $ruleData['id'] = $rule->id;
                $ruleData['day'] = $rule->day;
                $ruleData['is_available'] = $rule->is_available;
                $ruleData['hours'] = [];

                foreach ($rule->times as $time) {
                    $ruleData['hours'][] = [
                        'id' => $time->id,
                        'started_time' => Str::substr($time->started_time, 0 ,5),
                        'ended_time' => Str::substr($time->ended_time, 0 ,5),
                    ];
                }
            } else {
                $ruleData = [
                    'day' => $day,
                    'is_available' => false,
                    'hours' => [],
                ];
            }

            $weeklyHours[$day] = $ruleData;
        }

        return $weeklyHours;
    }

    public function weekdays($long = false): Collection
    {
        $weekdays = collect();
        $format = $long ? 'l' : 'D';

        for ($i = 1; $i <= 7; $i++) {
            $weekdays->put($i, [
                'id' => $i,
                'value' => date($format, strtotime("Sunday +{$i} days")),
            ]);
        }

        return $weekdays;
    }

    public function dateOverrides(Product $product): Collection
    {
        if (!$product->eventSchedule) {
            return collect();
        }

        return $product
            ->eventSchedule
            ->dateOverrides()
            ->select([
                'id',
                'started_date',
                'ended_date',
                'is_available',
            ])
            ->with('times', function ($query) {
                $query->select([
                    'id',
                    'started_time',
                    'ended_time',
                    'schedule_rule_id'
                ]);
            })
            ->get()
            ->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'started_date' => $rule->formattedStartedDate,
                    'ended_date' => $rule->formattedEndedDate,
                    'is_available' => $rule->is_available,
                    'displayDates' => $rule->displayDates,
                    'times' => $rule->times->map(function ($time) {
                        $time->started_time = Str::substr($time->started_time, 0, 5);
                        if ($time->ended_time) {
                            $time->ended_time = Str::substr($time->ended_time, 0, 5);
                        }
                        return $time;
                    }),
                ];
            });
    }

    private function diffEntitiesAgainstInputs(Collection $entities, Collection $inputs): Collection
    {
        return $entities->pluck('id')->diff(
            $inputs->whereNotNull('id')->pluck('id')->all()
        );
    }

    private function removeUnusedEntities(Collection $entities, Collection $inputs)
    {
        $unusedIds = $this->diffEntitiesAgainstInputs($entities, $inputs);

        if (!empty($unusedIds)) {
            $unusedEntities = $entities->whereIn('id', $unusedIds);

            foreach ($unusedEntities as $unusedEntity) {
                $unusedEntity->delete();
            }
        }
    }

    public function saveWeeklyHours(array $weeklyHourInputs, Schedule $schedule)
    {
        $weeklyHourRules = $schedule->weeklyHours;

        foreach ($weeklyHourInputs as $inputRule) {
            $weeklyHourRule = $weeklyHourRules
                ->first(fn ($rule) => $rule->day == $inputRule['day']);

            if (is_null($weeklyHourRule)) {
                $weeklyHourRule = ScheduleRule::factory()
                    ->state([
                        'day' => $inputRule['day'],
                        'type' => ScheduleRule::TYPE_WEEKLY_HOUR,
                        'schedule_id' => $schedule->id,
                    ])
                    ->make();
            }

            $weeklyHourRule->is_available = $inputRule['is_available'];
            $weeklyHourRule->save();


            $hours = collect($inputRule['hours']);
            $scheduleRuleTimes = $weeklyHourRule->times;

            $this->removeUnusedEntities($scheduleRuleTimes, $hours);

            foreach ($hours as $hour) {
                $scheduleRuleTime = null;

                if (!empty($hour['id'])) {
                    $scheduleRuleTime = $scheduleRuleTimes->firstWhere('id', $hour['id']);

                    if (is_null($scheduleRuleTime)) {
                        continue;
                    }
                }

                if (is_null($scheduleRuleTime)) {
                    $scheduleRuleTime = ScheduleRuleTime::factory()->state([
                        'schedule_rule_id' => $weeklyHourRule->id,
                    ])->make();
                }

                $scheduleRuleTime->started_time = $hour['started_time'];
                $scheduleRuleTime->ended_time = $hour['ended_time'];
                $scheduleRuleTime->save();
            }
        }
    }

    public function saveDateOverrides(Collection $dateOverrideInputs, Schedule $schedule)
    {
        $dateOverrideRules = $schedule->dateOverrides;

        $this->removeUnusedEntities($dateOverrideRules, $dateOverrideInputs);

        foreach ($dateOverrideInputs as $inputRule) {
            if (!empty($inputRule['id'])) {
                $dateOverrideRule = $dateOverrideRules
                    ->first(fn ($rule) => $rule->id == $inputRule['id']);

                if (is_null($dateOverrideRule)) {
                    continue;
                }

                $dateOverrideRule->started_date = $inputRule['started_date'];
                $dateOverrideRule->ended_date = $inputRule['ended_date'];
                $dateOverrideRule->is_available = !empty($inputRule['times']);

            } else {
                $dateOverrideRule = ScheduleRule::factory()
                    ->state([
                        'schedule_id' => $schedule->id,
                        'started_date' => $inputRule['started_date'],
                        'ended_date' => $inputRule['ended_date'],
                        'type' => ScheduleRule::TYPE_DATE_OVERRIDE,
                        'is_available' => !empty($inputRule['times']),
                    ])
                    ->make();
            }

            $dateOverrideRule->save();

            $scheduleRuleTimes = $dateOverrideRule->times;

            $inputTimes = collect($inputRule['times']);

            $this->removeUnusedEntities($scheduleRuleTimes, $inputTimes);

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
    }
}
