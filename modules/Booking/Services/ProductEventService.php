<?php

namespace Modules\Booking\Services;

use App\Services\CountryService;
use App\Helpers\GoogleMap;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Entities\ScheduleRule;
use Modules\Booking\Entities\ScheduleRuleTime;
use Modules\Ecommerce\Entities\Product;

class ProductEventService
{
    private $cacheProducts = null;
    private $cacheFrontendProducts = null;

    public function availableTimesRouteName(): string
    {
        return "booking.products.available-times";
    }

    public function availableTimesOrderRouteName(): string
    {
        return "admin.booking.orders.available-times";
    }

    public function allowedDatesRouteName(): string
    {
        return "booking.products.allowed-dates";
    }

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

    public function formResource(Product $product): array
    {
        return [
            'duration' => $product->duration,
            'bookable_date_range_type' => $product->bookable_date_range_type,
            'bookable_date_range' => $product->bookable_date_range,
            'timezone' => $product->eventSchedule->timezone ?? null,
            'pitch_started_at' => $product->getMeta('pitch_started_at'),
            'pitch_ended_at' => $product->getMeta('pitch_ended_at'),
            'pitch_timezone' => $product->getMeta('pitch_timezone'),
            'location' => $product->locations[0] ?? [
                'address' => null,
                'latitude' => null,
                'longitude' => null,
                'city' => null,
                'country_code' => null,
            ],
        ];
    }

    public function detailResource(Product $product): array
    {
        $schedule = $product->eventSchedule;

        return [
            'duration' => $this->displayDuration($product),
            'bookable_date_range_type' => $product->bookable_date_range_type,
            'bookable_date_range' => $product->bookable_date_range,
            'pitch_started_at' => $product->getMeta('pitch_started_at'),
            'pitch_ended_at' => $product->getMeta('pitch_ended_at'),
            'pitch_timezone' => $product->getMeta('pitch_timezone'),
            'location' => $product->locations[0] ?? null,
            'timezone' => $schedule->timezone,
            'display_timezone' => $schedule->displayTimezone,
        ];
    }

    private function displayDuration($product): string
    {
        return $product->duration.' '.Str::plural(
            $product->duration_unit,
            $product->duration
        );
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

    public function weeklyHoursForSchedule(Schedule $schedule): array
    {
        $weeklyHours = [];
        $rules = $schedule->weeklyHours ?? null;

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
                    'is_available' => $rule->is_available,
                    'display_dates' => $rule->displayDates,
                    'times' => $rule->times->map(function ($time) {
                        $timeArray = [];
                        $timeArray['started_time'] = Str::substr($time->started_time, 0, 5);
                        $timeArray['ended_time'] = !empty($time->ended_time) ? Str::substr($time->ended_time, 0, 5) : null;

                        return $timeArray;
                    }),
                ];
            });
    }

    public function pitchScheduleInfo(Product $product): array
    {
        $schedule = $product->eventSchedule;
        
        if (!$schedule) {
            return [
                'timezone' => 'Not set',
                'dateRange' => 'Not set',
                'availableDays' => 'Not set',
                'availableHours' => 'Not set',
                'startDate' => null,
                'endDate' => null,
                'availableDaysArray' => [],
                'weeklyHoursData' => [],
            ];
        }

        // Get available days
        $weeklyHours = $schedule->weeklyHours->where('is_available', true);
        $availableDays = $weeklyHours->map(function ($rule) {
            return $this->weekdays()->get($rule->day)['value'] ?? '';
        })->filter()->values()->implode(', ');

        // Get available day numbers (1-7, where 1 = Monday)
        $availableDaysArray = $weeklyHours->pluck('day')->values()->toArray();

        // Get weekly hours with time ranges for each day
        $weeklyHoursData = [];
        foreach ($schedule->weeklyHours as $rule) {
            if ($rule->is_available && $rule->times->isNotEmpty()) {
                $weeklyHoursData[$rule->day] = $rule->times->map(function ($time) {
                    return [
                        'started_time' => Str::substr($time->started_time, 0, 5),
                        'ended_time' => Str::substr($time->ended_time, 0, 5),
                    ];
                })->toArray();
            }
        }

        // Get typical hours (from first available day)
        $firstAvailableDay = $weeklyHours->first();
        $availableHours = 'Not set';
        
        if ($firstAvailableDay && $firstAvailableDay->times->isNotEmpty()) {
            $times = $firstAvailableDay->times->map(function ($time) {
                return Str::substr($time->started_time, 0, 5) . '-' . Str::substr($time->ended_time, 0, 5);
            })->implode(', ');
            $availableHours = $times;
        }

        // Get date range
        $pitchStart = $product->getMeta('pitch_started_at');
        $pitchEnd = $product->getMeta('pitch_ended_at');
        $dateRange = 'Not set';
        
        if ($pitchStart && $pitchEnd) {
            $dateRange = Carbon::parse($pitchStart)->format('Y-m-d') . ' to ' . Carbon::parse($pitchEnd)->format('Y-m-d');
        }

        return [
            'timezone' => $schedule->timezone ?? 'UTC',
            'dateRange' => $dateRange,
            'availableDays' => $availableDays ?: 'None',
            'availableHours' => $availableHours,
            'startDate' => $pitchStart ? Carbon::parse($pitchStart)->format('Y-m-d') : null,
            'endDate' => $pitchEnd ? Carbon::parse($pitchEnd)->format('Y-m-d') : null,
            'availableDaysArray' => $availableDaysArray,
            'weeklyHoursData' => $weeklyHoursData,
        ];
    }

    public function dateOverridesForSchedule(Schedule $schedule): Collection
    {
        return $schedule
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
                    'is_available' => $rule->is_available,
                    'display_dates' => $rule->displayDates,
                    'times' => $rule->times->map(function ($time) {
                        $timeArray = [];
                        $timeArray['started_time'] = Str::substr($time->started_time, 0, 5);
                        $timeArray['ended_time'] = !empty($time->ended_time) ? Str::substr($time->ended_time, 0, 5) : null;

                        return $timeArray;
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

    public function saveDateOverrides(
        Collection $dateOverrideInputs,
        Schedule $schedule
    ) {
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
                $dateOverrideRule->ended_date = null;
                $dateOverrideRule->is_available = !empty($inputRule['times']);

            } else {
                $dateOverrideRule = ScheduleRule::factory()
                    ->state([
                        'schedule_id' => $schedule->id,
                        'started_date' => $inputRule['started_date'],
                        'ended_date' => null,
                        'type' => ScheduleRule::TYPE_DATE_OVERRIDE,
                        'is_available' => !empty($inputRule['times']),
                    ])
                    ->make();
            }

            $dateOverrideRule->save();

            $scheduleRuleTimes = $dateOverrideRule->times;

            $inputTimes = collect($inputRule['times']);

            $leftovers = $scheduleRuleTimes->count() - $inputTimes->count();

            if ($leftovers > 0) {
                $chunk = $scheduleRuleTimes->shift($leftovers);

                if ($chunk instanceof Collection) {
                    $chunk->each(function ($time) {
                        $time->delete();
                    });
                } elseif ($chunk instanceof ScheduleRuleTime) {
                    $chunk->delete();
                }
            }

            foreach ($inputTimes as $index => $inputTime) {
                $scheduleRuleTime = $scheduleRuleTimes[ $index ]
                    ?? ScheduleRuleTime::factory()
                        ->state(['schedule_rule_id' => $dateOverrideRule->id])
                        ->make();

                $scheduleRuleTime->started_time = $inputTime['started_time'];
                $scheduleRuleTime->ended_time = $inputTime['ended_time'];
                $scheduleRuleTime->save();
            }
        }
    }

    public function minBookableDate(?Product $product = null): ?Carbon
    {
        $minDate = today();

        if ($product) {
            $pitchStart = $product->getMeta('pitch_started_at');
            if ($pitchStart) {
                $pitchStartDate = Carbon::parse($pitchStart)->startOfDay();
                if ($pitchStartDate->gt($minDate)) {
                    $minDate = $pitchStartDate;
                }
            }
        }

        return $minDate;
    }

    public function maxBookableDate(Product $product): ?Carbon
    {
        $pitchEnd = $product->getMeta('pitch_ended_at');
        if ($pitchEnd) {
            return Carbon::parse($pitchEnd)->endOfDay();
        }

        return today()->addDays($product->bookable_date_range);
    }

    public function getGoogleMapDirectionUrl(Product $product, ?array $origin = null): ?string
    {
        $location = $product->locations[0] ?? null;

        if (
            !$location
            || empty($location['latitude'])
            || empty($location['longitude'])
        ) {
            return null;
        }

        return GoogleMap::directionUrl(
            $location['latitude'],
            $location['longitude'],
            Arr::get($origin, 'latitude'),
            Arr::get($origin, 'longitude')
        );
    }

    private function getProductLocations(
        User $user = null,
        array $options = []
    ): Collection {
        $isUserProductManager = false;

        if (Arr::get($options, 'hasProductManager', false)) {
            $isUserProductManager = $user->isProductManager();
        }

        $hasRole = Arr::get($options, 'hasRole', false);

        $products = Product::with([
                'metas' => function ($q){
                    $q->whereIn('key', ['locations']);
                },
            ])
            ->type('Event')
            ->whereHas('metas', function ($query) use ($user, $hasRole) {
                if ($hasRole) {
                    $roleIds = $user->roles->pluck('id');

                    if ($roleIds->isNotEmpty()) {
                        $query
                            ->where('key', 'roles')
                            ->whereJsonContains('value', $roleIds);
                    } else {
                        $query
                            ->where('key', 'roles')
                            ->whereJsonLength('value', 0);
                    }
                } else {
                    $query->where('key', 'locations');
                }
            })
            ->when($isUserProductManager, function ($query) use ($user) {
                $query->whereHas('managers', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->get(['id', 'product_type_id']);

        return $products
            ->map(function ($product) {
                return collect($product->locations[0] ?? [])
                    ->only(['country_code', 'city'])
                    ->all();
            })
            ->filter()
            ->unique()
            ->values();
    }

    public function getCountryOptions(): array
    {
        if (! $this->cacheProducts) {
            $this->cacheProducts = $this->getProductLocations(
                auth()->user(),
                [
                    'hasProductManager' => true,
                    'hasRole' => false,
                ]
            );
        }

        return $this->mapCountryOptions($this->cacheProducts);
    }

    public function getCityOptions(): array
    {
        if (! $this->cacheProducts) {
            $this->cacheProducts = $this->getProductLocations(
                auth()->user(),
                [
                    'hasProductManager' => true,
                    'hasRole' => false,
                ]
            );
        }

        return $this->mapCityOptions($this->cacheProducts);
    }

    public function getFrontendCountryOptions(): array
    {
        if (! $this->cacheFrontendProducts) {
            $this->cacheFrontendProducts = $this->getProductLocations(
                auth()->user(),
                [
                    'hasProductManager' => false,
                    'hasRole' => true,
                ]
            );
        }

        return $this->mapCountryOptions($this->cacheFrontendProducts);
    }

    public function getFrontendCityOptions(): array
    {
        if (! $this->cacheFrontendProducts) {
            $this->cacheFrontendProducts = $this->getProductLocations(
                auth()->user(),
                [
                    'hasProductManager' => false,
                    'hasRole' => true,
                ]
            );
        }

        return $this->mapCityOptions($this->cacheFrontendProducts);
    }

    private function mapCountryOptions(Collection $products): array
    {
        $countryCodes = $products
            ->pluck('country_code')
            ->unique()
            ->values();

        return $countryCodes->transform(function ($code) {
                $countryName = app(CountryService::class)->getCountryName($code);

                return [
                    'value' => $code,
                    'name' => $countryName,
                ];
            })
            ->all();
    }

    private function mapCityOptions(Collection $products): array
    {
        return $products
            ->pluck('city')
            ->unique()
            ->values()
            ->map(function ($city) use ($products) {
                $countryCode = $products
                    ->where('city', $city)
                    ->first()['country_code']
                    ?? null;

                return [
                    'value' => $city,
                    'name' => $city,
                    'country_code' => $countryCode,
                ];
            })
            ->all();
    }

    public function getPublishedProducts(): EloquentCollection
    {
        return Product::published()->type('Event')->get();
    }

    public function getManagedProducts(): EloquentCollection
    {
        return Product::type('Event')->whereHas('managers')->get();
    }
}
