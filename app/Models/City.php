<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_code',
        'latitude',
        'longitude',
        'state_code',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'city_user');
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    public function spaceEvents(): HasMany
    {
        return $this->hasMany(SpaceEvent::class);
    }
}
