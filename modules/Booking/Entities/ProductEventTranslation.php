<?php

namespace Modules\Booking\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEventTranslation extends Model
{
    use HasFactory;

    protected $table = 'product_event_translations';

    protected $fillable = [
        'description',
        'excerpt',
        'locale',
    ];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\ProductEventTranslationFactory::new();
    }
}
