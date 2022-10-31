<?php

namespace Modules\Booking\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Booking\Entities\ScheduleRule;

class ScheduleRuleTimeFactory extends Factory
{
    protected $model = \Modules\Booking\Entities\ScheduleRuleTime::class;

    public function definition()
    {
        return [
            'started_time' => '09:00',
            'ended_time' => '17:00',
            'schedule_rule_id' => ScheduleRule::factory(),
        ];
    }
}
