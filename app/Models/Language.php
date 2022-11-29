<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeShown($query)
    {
        return $query->where('is_shown', true);
    }

    public function scopeCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}
