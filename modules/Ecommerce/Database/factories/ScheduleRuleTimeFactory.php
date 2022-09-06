<?php

namespace Modules\Ecommerce\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Ecommerce\Entities\ScheduleRule;

class ScheduleRuleTimeFactory extends Factory
{
    protected $model = \Modules\Ecommerce\Entities\ScheduleRuleTime::class;

    public function definition()
    {
        return [
            'started_time' => '09:00',
            'ended_time' => '17:00',
            'schedule_rule_id' => ScheduleRule::factory(),
        ];
    }
}
