<?php

namespace Modules\FormBuilder\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FieldGroupSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'field_group_id'
    ];
}
