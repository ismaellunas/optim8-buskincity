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
            'schedule_rule_id' => ScheduleRule::factory(),
        ];
    }
}
