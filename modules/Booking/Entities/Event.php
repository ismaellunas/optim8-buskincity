<?php

namespace Modules\Booking\Entities;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\Booking\Enums\BookingStatus;
use Modules\Booking\Helpers\EventTimeHelper;
use Modules\Ecommerce\Entities\OrderLine;

class Event extends BaseModel
{
    use HasFactory;

    protected $table = 'events';

    protected $dates = [
        'booked_at',
    ];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\EventFactory::new();
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function orderLine()
    {
        return $this->belongsTo(OrderLine::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', BookingStatus::UPCOMING);
    }

    public function scopePassed($query)
    {
        return $query->where('status', BookingStatus::PASSED);
    }

    public function scopeInStatus($query, array $status)
    {
        return $query->whereIn('status', $status);
    }

    public function scopeDateRange($query, array $dates)
    {
        $dates = array_filter($dates);

        sort($dates);

        if (count($dates) == 1) {
            return $query->whereDate('booked_at', $dates[0]);
        }

        return $query
            ->whereDate('booked_at', '>=', $dates[0])
            ->whereDate('booked_at', '<=', $dates[1]);
    }

    public function scopeOrderByTimezone($query, $timezone = 'UTC', $direction = 'ASC')
    {
        $eventTable = Event::getTableName();
        $scheduleTable = Schedule::getTableName();

        $timezoneSubQuery = (
            "SELECT {$scheduleTable}.timezone ".
            "FROM {$scheduleTable} ".
            "WHERE {$scheduleTable}.id = {$eventTable}.schedule_id"
        );

        $query->orderByRaw("(($eventTable.booked_at AT TIME ZONE ($timezoneSubQuery)) AT TIME ZONE '$timezone') $direction");
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
        return $this->booked_at->shiftTimezone($this->schedule->timezone);
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
