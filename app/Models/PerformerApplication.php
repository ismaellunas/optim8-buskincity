<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class PerformerApplication extends Model
{
    use HasFactory;

    const STATUS_EMAILED = 1;

    protected $casts = [
        'data' => AsCollection::class,
    ];
}
