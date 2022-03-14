<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    const PAYMENT_METHOD_STRIPE = 1;

    use HasFactory;

    protected $fillable = [
        'data',
        'event_type',
        'payment_method',
        'receiver_id',
    ];
}
