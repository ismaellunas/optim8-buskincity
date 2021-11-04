<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value'
    ];

    protected $casts = [
        'updated_at' => 'datetime:M d, Y \a\t h:i',
    ];
}
