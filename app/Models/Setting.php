<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public function scopeGroup($query, string $groupName)
    {
        return $query->where('group', $groupName);
    }
}
