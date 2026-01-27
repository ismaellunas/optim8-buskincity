<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleRuleTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_rule_id',
        'started_time',
        'ended_time',
    ];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\ScheduleRuleTimeFactory::new();
    }

    public function scheduleRule()
    {
        return $this->belongsTo(ScheduleRule::class);
    }
}
