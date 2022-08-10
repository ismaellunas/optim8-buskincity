<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GlobalOption extends BaseModel
{
    use HasFactory;

    public function saveFromInputs(array $inputs): void
    {
        $this->name = $inputs['name'];
        $this->type = $inputs['type'];
        $this->default_value = $inputs['default_value'] ?? null;
        $this->save();
    }

    public function scopeType($query, $value)
    {
        $query->where('type', $value);
    }
}
