<?php

namespace Modules\Ecommerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Ecommerce\Entities\Product;
use Modules\Booking\Entities\ScheduleRule;
use Modules\Booking\Entities\ScheduleRuleTime;
use Modules\Ecommerce\Enums\ProductStatus;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::orderBy('id', 'DESC')
            ->whereHas('productType', function ($query) {
                $query->where('name', 'Event');
            })
            ->whereStatus(ProductStatus::PUBLISHED->value)
            ->limit(5)
            ->get();

        foreach ($products as $product) {
            $schedule = $product->eventSchedule;

            for ($weekDay = 1; $weekDay <= 7; $weekDay ++) {
                $scheduleRule = ScheduleRule::factory()
                    ->available()
                    ->dayOfWeek($weekDay)
                    ->weeklyHourType()
                    ->for($schedule)
                    ->create();

                ScheduleRuleTime::factory()->for($scheduleRule)->create();
            }

            ScheduleRule::factory()
                ->available(false)
                ->dateOverrideType()
                ->dateRange(today()->endOfWeek())
                ->for($schedule)
                ->create();
        }
    }
}
