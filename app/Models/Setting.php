<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public function scopeGroup($query, string $groupName)
    {
        return $query->where('group', $groupName);
    }

    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
