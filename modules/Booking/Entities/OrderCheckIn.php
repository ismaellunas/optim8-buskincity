<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $dates = [
        'checked_in_at',
    ];

    protected $casts = [
        'data' => 'array',
        'geolocation' => 'array',
    ];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\OrderCheckInFactory::new();
    }
}
