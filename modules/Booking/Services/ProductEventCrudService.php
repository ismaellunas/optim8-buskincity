<?php

namespace Modules\Booking\Services;

use App\Enums\PublishingStatus;
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
            // If weekly_hours provided, use them; otherwise inherit from product
            if (!empty($inputs['weekly_hours'])) {
                $this->createEventScheduleFromInput($event, $inputs);
            } else {
                $this->createEventScheduleFromProduct($event, $product);
            }
        }

        return $event;
    }

    /**
     * Validate that event dates are within Pitch schedule constraints
     */
    private function validateEventWithinPitchSchedule(Product $product, array $inputs): void
    {
        $pitchStart = $product->getMeta('pitch_started_at');
        $pitchEnd = $product->getMeta('pitch_ended_at');
        
        if ($pitchStart && $pitchEnd) {
            $eventStart = Carbon::parse($inputs['started_at']);
            $eventEnd = Carbon::parse($inputs['ended_at']);
            $pitchStartDate = Carbon::parse($pitchStart);
            $pitchEndDate = Carbon::parse($pitchEnd);
            
            if ($eventStart->lt($pitchStartDate) || $eventEnd->gt($pitchEndDate)) {
                throw ValidationException::withMessages([
                    'started_at' => 'Event dates must be within Pitch date range (' . 
                                   $pitchStartDate->format('Y-m-d') . ' to ' . 
                                   $pitchEndDate->format('Y-m-d') . ')',
                ]);
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
        
        // Create event schedule with same timezone
        $eventSchedule = Schedule::factory()->state([
            'schedulable_type' => ProductEvent::class,
            'schedulable_id' => $event->id,
            'timezone' => $productSchedule->timezone,
        ])->create();
        
        // Copy weekly hours from product schedule
        foreach ($productSchedule->weeklyHours as $weeklyHour) {
            $newRule = ScheduleRule::create([
                'schedule_id' => $eventSchedule->id,
                'type' => $weeklyHour->type,
                'day' => $weeklyHour->day,
                'is_available' => $weeklyHour->is_available,
            ]);
            
            // Copy time ranges
            foreach ($weeklyHour->times as $time) {
                ScheduleRuleTime::create([
                    'schedule_rule_id' => $newRule->id,
                    'started_time' => $time->started_time,
                    'ended_time' => $time->ended_time,
                ]);
            }
        }
        
        // Copy date overrides that fall within event date range
        foreach ($productSchedule->dateOverrides as $override) {
            $overrideDate = Carbon::parse($override->started_date);
            
            // Only copy overrides that are within the event's date range
            if ($overrideDate->between($event->started_at, $event->ended_at)) {
                $newOverride = ScheduleRule::create([
                    'schedule_id' => $eventSchedule->id,
                    'type' => $override->type,
                    'started_date' => $override->started_date,
                    'ended_date' => $override->ended_date,
                    'is_available' => $override->is_available,
                ]);
                
                // Copy time ranges for the override
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

    /**
     * Create event schedule from user input (weekly_hours)
     */
    private function createEventScheduleFromInput(ProductEvent $event, array $inputs): void
    {
        // Create event schedule
        $eventSchedule = Schedule::factory()->state([
            'schedulable_type' => ProductEvent::class,
            'schedulable_id' => $event->id,
            'timezone' => $event->timezone,
        ])->create();
        
        // Save weekly hours from input
        $weeklyHours = $inputs['weekly_hours'] ?? [];
        
        foreach ($weeklyHours as $day => $weeklyHour) {
            if (!empty($weeklyHour['is_available'])) {
                $newRule = ScheduleRule::create([
                    'schedule_id' => $eventSchedule->id,
                    'type' => 'weekly_hours',
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

        // Update schedule if weekly_hours provided
        if (!empty($inputs['weekly_hours'])) {
            $this->updateEventSchedule($event, $inputs);
        }

        return $saved;
    }

    /**
     * Update event schedule with new weekly hours
     */
    private function updateEventSchedule(ProductEvent $event, array $inputs): void
    {
        if (!$event->schedule) {
            $this->createEventScheduleFromInput($event, $inputs);
            return;
        }

        // Delete existing weekly hours
        $event->schedule->weeklyHours()->delete();

        // Save new weekly hours from input
        $weeklyHours = $inputs['weekly_hours'] ?? [];
        
        foreach ($weeklyHours as $day => $weeklyHour) {
            if (!empty($weeklyHour['is_available'])) {
                $newRule = ScheduleRule::create([
                    'schedule_id' => $event->schedule->id,
                    'type' => 'weekly_hours',
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
    }
}
