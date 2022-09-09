<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ecommerce\Entities\ScheduleRuleTime;

class ScheduleRule extends Model
{
    use HasFactory;

    const TYPE_WEEKLY_HOUR = 'weekly_hour';
    const TYPE_DATE_OVERRIDE = 'date_override';

    protected $fillable = [];

    protected $dates = [
        'started_date',
        'ended_date',
    ];

    protected static function newFactory()
    {
        return \Modules\Ecommerce\Database\factories\ScheduleRuleFactory::new();
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function times()
    {
        return $this->hasMany(ScheduleRuleTime::class);
    }

    public function scopeAvailable($query, $isAvailable = true)
    {
        return $query->where('is_available', $isAvailable);
    }

    public function getDisplayDatesAttribute(): string
    {
        $format = 'j M Y';

        return $this->started_date->format($format)
            .($this->ended_date ? ' - '.$this->ended_date->format($format) : '');
    }

    public function getFormattedStartedDateAttribute(): string
    {
        return $this->started_date->toDateString();
    }

    public function getFormattedEndedDateAttribute(): string|null
    {
        return $this->ended_date ? $this->ended_date->toDateString() : null;
    }
}
