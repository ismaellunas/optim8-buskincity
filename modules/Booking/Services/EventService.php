<?php

namespace Modules\Booking\Services;

use App\Helpers\GoogleMap;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mews\Purifier\Facades\Purifier;
use Modules\Booking\Entities\Event;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Entities\ScheduleRule;
use Modules\Booking\Enums\BookingStatus;
use Modules\Booking\Helpers\EventTimeHelper;

class EventService
{
    private $cacheUserUpcomingEventCityOptions;

    private function scheduleDateOverrideDates(
        Schedule $schedule,
        Carbon $minDate,
        Carbon $maxDate,
        bool $availabiltiy = true,
    ): Collection {
        return $schedule
            ->dateOverrides()
            ->available($availabiltiy)
            ->where(function ($query) use ($minDate, $maxDate) {
                $query
                    ->whereBetween('started_date', [
                        $minDate->copy()->subDay(),
                        $maxDate->copy()->addDay()
                    ]);
            })
            ->orderBy('started_date')
            ->get(['id', 'started_date', 'type', 'is_available', 'schedule_id'])
            ->pluck('started_date')
            ->map(fn ($date) => $date->toDateString());
    }

    private function disabledDateOverrides(
        Schedule $schedule,
        Carbon $minDate,
        Carbon $maxDate
    ): Collection {
        return $this->scheduleDateOverrideDates(
            $schedule,
            $minDate,
            $maxDate,
            false
        );
    }

    private function availableDateOverrides(
        Schedule $schedule,
        Carbon $minDate,
        Carbon $maxDate
    ): Collection {
        return $this->scheduleDateOverrideDates(
            $schedule,
            $minDate,
            $maxDate
        );
    }

    private function listDates(Carbon $minDate, Carbon $maxDate): Collection
    {
        $dates = collect();

        for ($date = $minDate->copy(); $date->lte($maxDate); $date->addDay()) {
            $dates->put($date->toDateString(), $date->copy());
        }

        return $dates;
    }

    private function disabledWeekDays(Schedule $schedule): Collection
    {
        return $schedule->weeklyHours->where('is_available', false)->pluck('day');
    }

    private function disabledWeekDates(
        Schedule $schedule,
        Carbon $minDate,
        Carbon $maxDate
    ): Collection {
        $dates = $this->listDates($minDate, $maxDate);

        $disabledWeekDays = $this->disabledWeekDays($schedule);

        return $dates
            ->filter(function ($date) use ($disabledWeekDays) {
                return $disabledWeekDays->contains($date->dayOfWeekIso);
            })
            ->keys();
    }

    public function allowedDatesBetween(
        Schedule $schedule,
        Carbon $minDate,
        Carbon $maxDate
    ): Collection {
        $disabledDates = $this->disabledDateOverrides($schedule, $minDate, $maxDate);

        $dates = $this->listDates($minDate, $maxDate);

        $dates = $dates->except($disabledDates);

        $disabledWeekDates = $this->disabledWeekDates($schedule, $minDate, $maxDate);

        $availableDateOverrides = $this->availableDateOverrides($schedule, $minDate, $maxDate);

        $dates = $dates->except($disabledWeekDates->diff($availableDateOverrides));

        return $dates->keys();
    }

    public function allowedDates(
        Schedule $schedule,
        string $month,
        string $year
    ): Collection {
        $minDate = Carbon::parse(implode('-', [$year, $month, "01"]));
        $maxDate = $minDate->copy()->endOfMonth();

        return $this->allowedDatesBetween($schedule, $minDate, $maxDate);
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

    private function scheduleRuleOnDate($schedule, Carbon $date): ?ScheduleRule
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

    public function availableTimes(Schedule $schedule, string $date): Collection
    {
        $times = collect();

        $timezone = $schedule->timezone;

        $date = Carbon::parse($date, $timezone);

        $todayTime = null;

        $rule = $this->scheduleRuleOnDate($schedule, $date);

        if (!$rule) {
            return $times;
        }

        $bookedTimes = $this->bookedTimes($schedule, $date);

        $product = $schedule->schedulable;
        $duration = $product->duration;
        $method = EventTimeHelper::calculateDurationMethodName($product->duration_unit);

        if (today($schedule->timezone)->equalTo($date)) {
            $todayTime = Carbon::now($schedule->timezone)->$method($duration);
        }

        foreach ($rule->times as $scheduleTime) {

            $latestTime = null;

            if ($times->isNotEmpty()) {
                $latestTime = Carbon::createFromTimeString((string) $times->last(), $timezone);
            }

            $startedTime = Carbon::createFromTimeString($scheduleTime->started_time, $timezone);

            if ($latestTime && $latestTime->isAfter($startedTime)) {
                $startedTime = $latestTime;
            }

            $endedTime = Carbon::createFromTimeString($scheduleTime->ended_time, $timezone);

            for ($i = $startedTime; $i->lte($endedTime); $i->$method($duration)) {
                if ($todayTime && $startedTime->isBefore($todayTime)) {
                    continue;
                }

                $currentEndTime = $i->copy();
                $currentEndTime->$method($duration);

                $isTimeBooked = $bookedTimes->contains(function ($booked) use ($i, $currentEndTime) {
                    return (
                        $i->equalTo($booked->startTime)
                        || (
                            $i->gte($booked->startTime)
                            && $i->isBefore($booked->endTime)
                        )
                        || (
                            $currentEndTime->isAfter($booked->startTime)
                            && $currentEndTime->isBefore($booked->endTime)
                        )
                    );
                });

                if (!$isTimeBooked && $currentEndTime->lte($endedTime)) {
                    $times->push($i->format('H:i'));
                }
            }
        }

        return $times;
    }

    private function scopeUpcomingEventsByUser(Builder $query,int $userId, $scopes = null)
    {
        $query
            ->upcoming()
            ->whereHas('orderLine.order', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    if ($scopeName == 'city') {
                        $query->whereHas(
                            'orderLine.purchasable.product',
                            function (Builder $query) use ($scopeName, $value) {
                                $query->$scopeName($value);
                            }
                        );
                    } else {
                        $query->$scopeName($value);
                    }
                }
            });
    }

    public function getNearestSearchableUpcomingEventByUser(int $userId): ?Carbon
    {
        $scopes['dateRange'] = [
            Carbon::today()->subWeek()->toDateString(),
            Carbon::today()->addYear()->toDateString(),
        ];

        $event = Event::where(function ($query) use ($userId, $scopes) {
            $this->scopeUpcomingEventsByUser($query, $userId, $scopes);
        })
            ->orderByTimezone('UTC')
            ->first();

        return $event ? $event->booked_at : null;
    }

    public function getUpcomingEventsByUser(
        int $userId,
        array $scopes = null,
        $perPage = 5
    ): LengthAwarePaginator {
        $events = Event::where(function ($query) use ($userId, $scopes) {
                $this->scopeUpcomingEventsByUser($query, $userId, $scopes);
            })
            ->with([
                'orderLine' => function ($query) {
                    $query->select('id', 'order_id', 'purchasable_id', 'purchasable_type');
                    $query->with('purchasable', function ($query) {
                        $query->select('id', 'product_id');
                        $query->with('product', function ($query) {
                            $query->select('id', 'attribute_data');
                            $query->with('metas', function ($query) {
                                $query->whereIn('key', ['locations']);
                            });
                        });
                    });
                },
                'schedule' => function ($query) {
                    $query->select('id', 'timezone');
                },
            ])
            ->orderBy('booked_at', 'ASC')
            ->paginate($perPage);

        $events->transform(function ($event) {
            $product = $event->orderLine->purchasable->product;
            $location = $product->locations[0] ?? [];

            return [
                'date' => $event->timezonedBookedAt->format('d M Y'),
                'time' => $event->displayStartEndTime,
                'timezone' => $event->schedule->timezone,
                'location' => $location,
                'name' => $event->orderLine->purchasable->product->displayName,
                'short_description' => nl2br(Purifier::clean($event->orderLine->purchasable->product->displayShortDescription)),
                'description' => nl2br(Purifier::clean($event->orderLine->purchasable->product->displayDescription)),
                'direction_url' => GoogleMap::directionUrl(
                    $location['latitude'],
                    $location['longitude']
                ),
            ];
        });

        return $events;
    }

    public function getUserUpcomingEventCityOptions(
        int $userId,
        string $noneLabel = null,
    ): Collection {
        if (is_null($this->cacheUserUpcomingEventCityOptions)) {
            $this->cacheUserUpcomingEventCityOptions = Event::
                where(function ($query) use ($userId ) {
                    $this->scopeUpcomingEventsByUser($query, $userId);
                })
                ->with([
                    'orderLine' => function ($query) {
                        $query->select('id', 'order_id', 'purchasable_id', 'purchasable_type');
                        $query->with('purchasable', function ($query) {
                            $query->select('id', 'product_id');
                            $query->with('product', function ($query) {
                                $query->select('id');
                                $query->with('metas', function ($query) {
                                    $query->whereIn('key', ['locations']);
                                });
                            });
                        });
                    },
                ])->get();
        }

        $options = $this->cacheUserUpcomingEventCityOptions
            ->pluck('orderLine.purchasable.product')
            ->unique(fn ($product) => $product->locations[0]['city'])
            ->map(fn ($product) => [
                'id' => $product->locations[0]['city'],
                'value' => $product->locations[0]['city'],
            ]);

        if (!is_null($noneLabel)) {
            $options->prepend(['id' => null, 'value' => $noneLabel]);
        }

        return $options->values();
    }
}
