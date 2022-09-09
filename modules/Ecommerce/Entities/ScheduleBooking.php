<?php

namespace Modules\Ecommerce\Entities;

use Carbon\Carbon;
use GetCandy\Models\OrderLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Ecommerce\Enums\BookingStatus;

class ScheduleBooking extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $dates = [
        'booked_at',
    ];

    protected static function newFactory()
    {
        return \Modules\Ecommerce\Database\factories\ScheduleBookingFactory::new();
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

    public function getEndedTimeAttribute(): Carbon
    {
        $bookedAt = $this->booked_at->copy();

        $method = 'add'.Str::title(Str::plural($this->duration_unit));

        return $bookedAt->$method($this->duration);
    }
}
