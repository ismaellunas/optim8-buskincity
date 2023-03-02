<?php

namespace App\Models;

use App\Traits\Mediable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends BaseModel
{
    use HasFactory;
    use Mediable;

    protected $fillable = [
        'display_name',
        'group',
        'key',
        'order',
        'value',
    ];

    public function scopeGroup($query, string $groupName)
    {
        return $query->where('group', $groupName);
    }

    public function scopeGroupPrefix($query, string $groupName)
    {
        return $query->where('group', 'ILIKE', $groupName.'%');
    }

    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
