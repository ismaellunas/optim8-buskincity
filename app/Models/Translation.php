<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];
    protected $appends = ["isEdit"];

    // Accessor
    public function getIsEditAttribute()
    {
        return false;
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
