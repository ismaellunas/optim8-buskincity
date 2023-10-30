<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'title',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_manageable' => 'boolean',
        'navigations' => 'array',
    ];

    protected function getDisplayStatusAttribute(): string
    {
        return $this->is_active ? __("Active") : __("Inactive");
    }

    protected function getDisplayTitleAttribute(): string
    {
        return __($this->title);
    }
}
