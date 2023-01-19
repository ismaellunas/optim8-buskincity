<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'type',
    ];

    protected $casts = [
        'setting' => 'array',
    ];

    public function newQuery(bool $excludeDeleted = true)
    {
        return $this->customNewQuery(
            parent::newQuery($excludeDeleted)
        );
    }

    protected function customNewQuery($newQuery): Builder
    {
        return $newQuery->whereNull('type');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function fieldGroups()
    {
        return $this->hasMany(FieldGroup::class, 'form_id');
    }
}
