<?php

namespace Modules\Booking\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Booking\Entities\ScheduleRule;

class Schedule extends BaseModel
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\ScheduleFactory::new();
    }

    public function rules()
    {
        return $this->hasMany(ScheduleRule::class);
    }

    public function weeklyHours()
    {
        return $this->rules()->where('type', ScheduleRule::TYPE_WEEKLY_HOUR);
    }

    public function dateOverrides()
    {
        return $this->rules()->where('type', ScheduleRule::TYPE_DATE_OVERRIDE);
    }

    public function schedulable()
    {
        return $this->morphTo();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
