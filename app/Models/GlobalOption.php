<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GlobalOption extends BaseModel
{
    use HasFactory;

    public function saveFromInputs(array $inputs): void
    {
        $this->name = $inputs['name'];
        $this->key = $inputs['key'];
        $this->default_value = $inputs['default_value'] ?? null;
        $this->save();
    }

    public function scopeKey($query, $value)
    {
        $query->where('key', $value);
    }
}
