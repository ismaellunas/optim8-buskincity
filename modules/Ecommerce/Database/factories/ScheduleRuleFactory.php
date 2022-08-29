<?php

namespace Modules\Ecommerce\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Ecommerce\Entities\Schedule;

class ScheduleRuleFactory extends Factory
{
    protected $model = \Modules\Ecommerce\Entities\ScheduleRule::class;

    public function definition()
    {
        return [
            'type' => $this->model::TYPE_WEEKLY_HOUR,
            'schedule_id' => Schedule::factory(),
        ];
    }
}
