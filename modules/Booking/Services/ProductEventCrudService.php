<?php

namespace Modules\Booking\Services;

use App\Enums\PublishingStatus;
use App\Services\CountryService;
use Carbon\Carbon;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Entities\ScheduleRule;
use Modules\Booking\Entities\ScheduleRuleTime;
use Modules\Booking\Entities\ProductEvent;
use Modules\Ecommerce\Entities\Product;

class ProductEventCrudService
{
    public function getFrontendRecords(
        ?string $term = null,
        array $scopes = [],
        int $perPage = 15
    ): AbstractPaginator {
        return ProductEvent::published()
            ->with(['product', 'schedule'])
            ->select([
                'id',
                'product_id',
                'title',
                'started_at',
                'ended_at',
                'city',
                'country_code',
                'address',
                'latitude',
                'longitude',
            ])
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->when($scopes['city'] ?? null, function ($query, $city) {
                $query->where('city', $city);
            })
            ->when($scopes['country'] ?? null, function ($query, $country) {
                $query->where('country_code', $country);
            })
            ->orderBy('started_at', 'ASC')
            ->orderBy('title')
            ->paginate($perPage);
    }

    public function transformFrontendRecords(AbstractPaginator $records): void
    {
        $locale = config('app.locale');

        $records->transform(function ($event) use ($locale) {
            $translation = $event->translateOrDefault($locale);

            return [
                'id' => $event->id,
                'title' => $event->title,
                'pitch_name' => $event->product?->translateAttribute('name', $locale),
                'description' => $translation?->description,
                'excerpt' => $translation?->excerpt,
                'started_at' => $event->started_at->format('M d, Y'),
                'ended_at' => $event->ended_at->format('M d, Y'),
                'city' => $event->city,
                'country' => $event->country_code,
                'address' => $event->address,
            ];
        });
    }

    public function getFrontendCountryOptions(): array
    {
        $events = ProductEvent::published()
            ->select('country_code')
            ->whereNotNull('country_code')
            ->distinct()
            ->get();

        return $events->map(function ($event) {
            return [
                'value' => $event->country_code,
                'name' => app(CountryService::class)->getCountryName($event->country_code),
            ];
        })->all();
    }

    public function getFrontendCityOptions(): array
    {
        return ProductEvent::published()
            ->select('city', 'country_code')
            ->whereNotNull('city')
            ->distinct()
            ->get()
            ->map(function ($event) {
                return [
                    'value' => $event->city,
                    'name' => $event->city,
                    'country_code' => $event->country_code,
                ];
            })
            ->all();
    }
    public function getRecords(
        Product $product,
        ?string $term = null,
        int $perPage = 10
    ): AbstractPaginator {
        return ProductEvent::orderBy('started_at', 'ASC')
            ->select([
                'id',
                'title',
                'started_at',
                'ended_at',
                'status',
            ])
            ->where('product_id', $product->id)
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->orderBy('started_at')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function transformRecords(AbstractPaginator $records)
    {
        $dateFormat = config('constants.format.date_time_minute');

        $records->transform(function ($event) use ($dateFormat) {
            return [
                ...$event->only([
                    'id',
                    'title',
                    'status',
                ]),
                ...[
                    'started_at' => $event->started_at->format($dateFormat),
                    'ended_at' => $event->ended_at->format($dateFormat),
                    'display_status' => $event->displayStatus,
                ]
            ];
        });
    }

    public function getEditableRecord(ProductEvent $event)
    {
        $event->load('translation', 'schedule.weeklyHours.times', 'schedule.dateOverrides.times');

        $weeklyHours = [];
        $dateOverrides = [];

        if ($event->schedule) {
            // Format weekly hours for frontend
            foreach ($event->schedule->weeklyHours as $rule) {
                $weeklyHours[$rule->day] = [
                    'day' => $rule->day,
                    'is_available' => $rule->is_available,
                    'hours' => $rule->times->map(function ($time) {
                        return [
                            'started_time' => $time->started_time,
                            'ended_time' => $time->ended_time,
                        ];
                    })->toArray(),
                ];
            }

            // Format date overrides for frontend
            foreach ($event->schedule->dateOverrides as $override) {
                $dateOverrides[] = [
                    'started_date' => $override->started_date,
                    'ended_date' => $override->ended_date,
                    'is_available' => $override->is_available,
                    'times' => $override->times->map(function ($time) {
                        return [
                            'started_time' => $time->started_time,
                            'ended_time' => $time->ended_time,
                        ];
                    })->toArray(),
                ];
            }
        }

        return [
            ...$event->only([
                'id',
                'title',
                'timezone',
                'status',
            ]),
            ...[
                'ended_at' => $event->ended_at->toIso8601String(),
                'started_at' => $event->started_at->toIso8601String(),
                'translations' => $event->getTranslationsArray(),
                'weekly_hours' => $weeklyHours,
                'date_overrides' => $dateOverrides,
                // Location inherited from product, not editable in event
            ]
        ];
    }

    public function createEvent(Product $product, array $inputs): ProductEvent
    {
        // Validate event is within Pitch schedule constraints
        $this->validateEventWithinPitchSchedule($product, $inputs);

        $event = new ProductEvent();
        $event->product_id = $product->id;
        $event->author_id = auth()->id();

        // Inherit timezone from product if not provided
        if (empty($inputs['timezone'])) {
            $inputs['timezone'] = $product->eventSchedule->timezone ?? 'UTC';
        }

        // Inherit location from product before updating
        if (!empty($product->locations[0])) {
            $location = $product->locations[0];
            $event->address = $location['address'] ?? null;
            $event->city = $location['city'] ?? null;
            $event->country_code = $location['country_code'] ?? null;
            $event->latitude = $location['latitude'] ?? null;
            $event->longitude = $location['longitude'] ?? null;
        }

        $this->updateEvent($event, $inputs);

        // Create event schedule
        if (!$event->schedule) {
            $hasWeeklyHours = $this->hasWeeklyHoursAvailability($inputs['weekly_hours'] ?? []);
            $hasDateOverrides = $this->hasDateOverrides($inputs['date_overrides'] ?? []);

            if ($hasWeeklyHours) {
                $this->createEventScheduleFromInput($event, $inputs);
            } else {
                $this->createEventScheduleFromProduct($event, $product);

                if ($hasDateOverrides) {
                    $this->applyDateOverrides($event->schedule, $inputs['date_overrides']);
                }
            }
        }

        return $event;
    }

    /**
     * Validate that event dates are within Pitch schedule constraints
     */
    private function validateEventWithinPitchSchedule(Product $product, array $inputs): void
    {
        $errors = [];
        
        // Validate date range
        $pitchStart = $product->getMeta('pitch_started_at');
        $pitchEnd = $product->getMeta('pitch_ended_at');
        
        if ($pitchStart && $pitchEnd) {
            // Get the pitch timezone for proper date comparison
            $pitchTimezone = $product->eventSchedule?->timezone ?? $inputs['timezone'] ?? 'UTC';
            
            // Parse dates in the pitch timezone and normalize to date only
            $eventStart = Carbon::parse($inputs['started_at'], $pitchTimezone)->startOfDay();
            $eventEnd = Carbon::parse($inputs['ended_at'], $pitchTimezone)->startOfDay();
            $pitchStartDate = Carbon::parse($pitchStart, $pitchTimezone)->startOfDay();
            $pitchEndDate = Carbon::parse($pitchEnd, $pitchTimezone)->startOfDay();
            
            // Check if event dates are outside the pitch date range
            if ($eventStart->lessThan($pitchStartDate) || $eventEnd->greaterThan($pitchEndDate)) {
                $errors['started_at'] = 'Event dates must be within Pitch date range (' . 
                                       $pitchStartDate->format('Y-m-d') . ' to ' . 
                                       $pitchEndDate->format('Y-m-d') . '). ' .
                                       'Selected: ' . $eventStart->format('Y-m-d') . ' to ' . $eventEnd->format('Y-m-d') .
                                       ' (in ' . $pitchTimezone . ' timezone)';
            }
        }
        
        // Validate weekly hours against pitch schedule
        $pitchSchedule = $product->eventSchedule;
        if ($pitchSchedule && !empty($inputs['weekly_hours'])) {
            $this->validateWeeklyHoursAgainstPitch($pitchSchedule, $inputs['weekly_hours'], $errors);
        }
        
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
    
    /**
     * Validate that event weekly hours are within Pitch schedule hours
     */
    private function validateWeeklyHoursAgainstPitch($pitchSchedule, array $weeklyHours, array &$errors): void
    {
        // Get pitch weekly hours indexed by day
        $pitchWeeklyHours = [];
        foreach ($pitchSchedule->weeklyHours as $rule) {
            if ($rule->is_available) {
                $pitchWeeklyHours[$rule->day] = $rule->times->map(function ($time) {
                    return [
                        'start' => Carbon::createFromFormat('H:i:s', $time->started_time),
                        'end' => Carbon::createFromFormat('H:i:s', $time->ended_time),
                    ];
                })->toArray();
            }
        }
        
        // Validate each event weekly hour against pitch schedule
        foreach ($weeklyHours as $day => $dayData) {
            if (!$dayData['is_available'] || empty($dayData['hours'])) {
                continue;
            }
            
            // Check if this day is available in pitch schedule
            if (!isset($pitchWeeklyHours[$day])) {
                $errors["weekly_hours.{$day}"] = "This day is not available in the Pitch schedule.";
                continue;
            }
            
            // Validate each time range
            foreach ($dayData['hours'] as $hourIdx => $timeRange) {
                $eventStart = Carbon::createFromFormat('H:i', substr($timeRange['started_time'], 0, 5));
                $eventEnd = Carbon::createFromFormat('H:i', substr($timeRange['ended_time'], 0, 5));
                
                $isWithinPitchHours = false;
                
                // Check if event time falls within any pitch time range
                foreach ($pitchWeeklyHours[$day] as $pitchRange) {
                    if ($eventStart->gte($pitchRange['start']) && $eventEnd->lte($pitchRange['end'])) {
                        $isWithinPitchHours = true;
                        break;
                    }
                }
                
                if (!$isWithinPitchHours) {
                    $allowedTimes = collect($pitchWeeklyHours[$day])->map(function ($range) {
                        return $range['start']->format('H:i') . '-' . $range['end']->format('H:i');
                    })->join(', ');
                    
                    $errors["weekly_hours.{$day}.hours.{$hourIdx}"] = 
                        "Time range must be within Pitch hours: {$allowedTimes}";
                }
            }
        }
    }

    /**
     * Create event schedule by inheriting from product schedule
     */
    private function createEventScheduleFromProduct(ProductEvent $event, Product $product): void
    {
        $productSchedule = $product->eventSchedule;
        
        if (!$productSchedule) {
            // Create default schedule if product has no schedule
            Schedule::factory()->state([
                'schedulable_type' => ProductEvent::class,
                'schedulable_id' => $event->id,
                'timezone' => $event->timezone,
            ])->create();
            return;
        }
        
        $eventSchedule = $this->ensureEventSchedule($event, $productSchedule->timezone);
        $eventSchedule->rules()->delete();
        
        $this->copyScheduleRules(
            $eventSchedule,
            $productSchedule,
            $event->started_at,
            $event->ended_at
        );
    }

    /**
     * Create event schedule from user input (weekly_hours)
     */
    private function createEventScheduleFromInput(ProductEvent $event, array $inputs): void
    {
        // Create event schedule
        $eventSchedule = $this->ensureEventSchedule($event, $event->timezone);
        
        // Save weekly hours from input
        $weeklyHours = $inputs['weekly_hours'] ?? [];
        
        foreach ($weeklyHours as $day => $weeklyHour) {
            if (!empty($weeklyHour['is_available'])) {
                $newRule = ScheduleRule::create([
                    'schedule_id' => $eventSchedule->id,
                    'type' => ScheduleRule::TYPE_WEEKLY_HOUR,
                    'day' => $day,
                    'is_available' => $weeklyHour['is_available'],
                ]);
                
                // Save time ranges
                foreach ($weeklyHour['hours'] ?? [] as $time) {
                    ScheduleRuleTime::create([
                        'schedule_rule_id' => $newRule->id,
                        'started_time' => $time['started_time'],
                        'ended_time' => $time['ended_time'],
                    ]);
                }
            }
        }

        $this->applyDateOverrides($eventSchedule, $inputs['date_overrides'] ?? []);
    }

    public function updateEvent(ProductEvent $event, array $inputs)
    {
        $event->title = Arr::get($inputs, 'title');
        $event->started_at = Arr::get($inputs, 'started_at');
        $event->ended_at = Arr::get($inputs, 'ended_at');
        $event->fill(Arr::get($inputs, 'translations', []));
        $event->timezone = Arr::get($inputs, 'timezone');
        $event->status = Arr::get($inputs, 'status', PublishingStatus::DRAFT->value);
        
        // Inherit location from product (Pitch)
        $product = $event->product;
        if ($product && !empty($product->locations[0])) {
            $location = $product->locations[0];
            $event->address = $location['address'] ?? null;
            $event->city = $location['city'] ?? null;
            $event->country_code = $location['country_code'] ?? null;
            $event->latitude = $location['latitude'] ?? null;
            $event->longitude = $location['longitude'] ?? null;
        }

        $saved = $event->save();

        if ($event->schedule && $event->timezone) {
            $event->schedule->timezone = $event->timezone;
            $event->schedule->save();
        }

        $hasWeeklyHours = $this->hasWeeklyHoursAvailability($inputs['weekly_hours'] ?? []);
        $hasDateOverrides = $this->hasDateOverrides($inputs['date_overrides'] ?? []);

        // Update schedule if inputs contain availability or overrides
        if ($hasWeeklyHours) {
            $this->updateEventSchedule($event, $inputs);
        } elseif ($hasDateOverrides) {
            $product = $event->product;
            if ($product) {
                $this->createEventScheduleFromProduct($event, $product);
                $this->applyDateOverrides($event->schedule, $inputs['date_overrides']);
            }
        } elseif (array_key_exists('weekly_hours', $inputs) || array_key_exists('date_overrides', $inputs)) {
            $product = $event->product;
            if ($product) {
                $this->createEventScheduleFromProduct($event, $product);
            }
        }

        return $saved;
    }

    /**
     * Update event schedule with new weekly hours
     */
    private function updateEventSchedule(ProductEvent $event, array $inputs): void
    {
        $eventSchedule = $this->ensureEventSchedule($event, $event->timezone);

        // Delete existing weekly hours
        $eventSchedule->weeklyHours()->delete();
        $eventSchedule->dateOverrides()->delete();

        // Save new weekly hours from input
        $weeklyHours = $inputs['weekly_hours'] ?? [];
        
        foreach ($weeklyHours as $day => $weeklyHour) {
            if (!empty($weeklyHour['is_available'])) {
                $newRule = ScheduleRule::create([
                    'schedule_id' => $eventSchedule->id,
                    'type' => ScheduleRule::TYPE_WEEKLY_HOUR,
                    'day' => $day,
                    'is_available' => $weeklyHour['is_available'],
                ]);
                
                // Save time ranges
                foreach ($weeklyHour['hours'] ?? [] as $time) {
                    ScheduleRuleTime::create([
                        'schedule_rule_id' => $newRule->id,
                        'started_time' => $time['started_time'],
                        'ended_time' => $time['ended_time'],
                    ]);
                }
            }
        }

        $this->applyDateOverrides($eventSchedule, $inputs['date_overrides'] ?? []);
    }

    private function hasWeeklyHoursAvailability(array $weeklyHours): bool
    {
        foreach ($weeklyHours as $weeklyHour) {
            if (!empty($weeklyHour['is_available']) && !empty($weeklyHour['hours'])) {
                return true;
            }
        }

        return false;
    }

    private function hasDateOverrides(array $dateOverrides): bool
    {
        return !empty($dateOverrides);
    }

    private function hasScheduleInput(array $inputs): bool
    {
        return $this->hasWeeklyHoursAvailability($inputs['weekly_hours'] ?? [])
            || $this->hasDateOverrides($inputs['date_overrides'] ?? []);
    }

    private function ensureEventSchedule(ProductEvent $event, ?string $timezone): Schedule
    {
        if ($event->schedule) {
            if ($timezone) {
                $event->schedule->timezone = $timezone;
                $event->schedule->save();
            }

            return $event->schedule;
        }

        return Schedule::factory()->state([
            'schedulable_type' => ProductEvent::class,
            'schedulable_id' => $event->id,
            'timezone' => $timezone ?? 'UTC',
        ])->create();
    }

    private function copyScheduleRules(
        Schedule $eventSchedule,
        Schedule $productSchedule,
        Carbon $eventStart,
        Carbon $eventEnd
    ): void {
        foreach ($productSchedule->weeklyHours as $weeklyHour) {
            $newRule = ScheduleRule::create([
                'schedule_id' => $eventSchedule->id,
                'type' => $weeklyHour->type,
                'day' => $weeklyHour->day,
                'is_available' => $weeklyHour->is_available,
            ]);
            
            foreach ($weeklyHour->times as $time) {
                ScheduleRuleTime::create([
                    'schedule_rule_id' => $newRule->id,
                    'started_time' => $time->started_time,
                    'ended_time' => $time->ended_time,
                ]);
            }
        }
        
        foreach ($productSchedule->dateOverrides as $override) {
            $overrideDate = Carbon::parse($override->started_date);
            
            if ($overrideDate->between($eventStart, $eventEnd)) {
                $newOverride = ScheduleRule::create([
                    'schedule_id' => $eventSchedule->id,
                    'type' => $override->type,
                    'started_date' => $override->started_date,
                    'ended_date' => $override->ended_date,
                    'is_available' => $override->is_available,
                ]);
                
                foreach ($override->times as $time) {
                    ScheduleRuleTime::create([
                        'schedule_rule_id' => $newOverride->id,
                        'started_time' => $time->started_time,
                        'ended_time' => $time->ended_time,
                    ]);
                }
            }
        }
    }

    private function applyDateOverrides(Schedule $eventSchedule, array $dateOverrides): void
    {
        foreach ($dateOverrides as $override) {
            $newOverride = ScheduleRule::create([
                'schedule_id' => $eventSchedule->id,
                'type' => ScheduleRule::TYPE_DATE_OVERRIDE,
                'started_date' => $override['started_date'] ?? null,
                'ended_date' => $override['ended_date'] ?? null,
                'is_available' => $override['is_available'] ?? false,
            ]);

            foreach ($override['times'] ?? [] as $time) {
                ScheduleRuleTime::create([
                    'schedule_rule_id' => $newOverride->id,
                    'started_time' => $time['started_time'] ?? null,
                    'ended_time' => $time['ended_time'] ?? null,
                ]);
            }
        }
    }
}
