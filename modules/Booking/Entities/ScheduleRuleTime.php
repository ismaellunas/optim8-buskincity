<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleRuleTime extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\ScheduleRuleTimeFactory::new();
    }

    public function scheduleRule()
    {
        return $this->belongsTo(ScheduleRule::class);
    }
}
