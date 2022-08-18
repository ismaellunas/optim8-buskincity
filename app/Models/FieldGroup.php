<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldGroup extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
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
}
