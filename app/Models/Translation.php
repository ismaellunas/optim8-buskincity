<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $table = 'ltm_translations';
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    // Scope
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
