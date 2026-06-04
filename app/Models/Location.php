<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'space_id',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
