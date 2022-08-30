<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ecommerce\Entities\ScheduleRule;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\Ecommerce\Database\factories\ScheduleFactory::new();
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
}
