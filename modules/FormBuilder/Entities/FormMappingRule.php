<?php

namespace Modules\FormBuilder\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormMappingRule extends Model
{
    use HasFactory;

    protected $casts = [
        'from' => 'array',
        'to' => 'array',
    ];

    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FormMappingRuleFactory::new();
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
