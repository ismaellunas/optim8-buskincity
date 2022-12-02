<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'url',
        'order',
        'icon',
        'is_blank',
        'parent_id',
        'menu_id',
        'menu_itemable_id',
        'menu_itemable_type',
    ];

    protected $attributes = [
        'menu_itemable_id' => null,
        'menu_itemable_type' => null,
    ];

    // Relation
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function menuItemable()
    {
        return $this->morphTo();
    }

    public function getIsPolymorphicExistsAttribute(): bool
    {
        return (
            !is_null($this->menu_itemable_id)
            && !is_null($this->menu_itemable_type)
        );
    }
}
