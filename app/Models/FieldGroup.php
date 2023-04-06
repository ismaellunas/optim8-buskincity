<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FieldGroup extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'fields' => 'array',
    ];
}
