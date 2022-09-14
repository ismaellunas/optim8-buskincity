<?php

namespace Modules\Ecommerce\Entities;

use Carbon\Carbon;
use GetCandy\Models\OrderLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Ecommerce\Helpers\EventTimeHelper;

class Event extends Model
{
    use HasFactory;

    protected $table = 'schedule_bookings';

    protected $fillable = [];

    protected $dates = [
        'booked_at',
    ];

    protected static function newFactory()
    {
        return \Modules\Ecommerce\Database\factories\EventFactory::new();
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function orderLine()
    {
        return $this->belongsTo(OrderLine::class);
    }

    public function getFormattedBookedAtAttribute(): string
    {
        return $this->booked_at->format(config('constants.format.date_time_minute'));
    }

    public function getDisplayDurationAttribute(): string
    {
        return $this->duration.' '.Str::plural(
            $this->duration_unit,
            $this->duration
        );
    }

    public function getTimezonedBookedAtAttribute(): Carbon
    {
        return $this->booked_at->setTimezone($this->schedule->timezone);
    }

    public function getEndedTimeAttribute(): Carbon
    {
        $bookedAt = $this->timezonedBookedAt->copy();

        $method = EventTimeHelper::calculateDurationMethodName($this->duration_unit);

        return $bookedAt->$method($this->duration);
    }

    public function getDisplayStartEndTimeAttribute(): string
    {
        return $this->timezonedBookedAt->format('G:i')
            .' - '
            .$this->endedTime->format('G:i');
    }
}
