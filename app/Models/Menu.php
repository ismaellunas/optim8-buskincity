<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
    ];

    const TYPE_HEADER = 1;
    const TYPE_FOOTER = 2;

    public function saveFromInputs(array $inputs)
    {
        $this->fill($inputs);
        $this->save();
    }

    // Scope
    public function scopeHeader($query)
    {
        return $query->where('type', self::TYPE_HEADER);
    }

    public function scopeFooter($query)
    {
        return $query->where('type', self::TYPE_FOOTER);
    }

    // Relation
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'menu_id');
    }
}
