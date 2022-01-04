<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];

    public function formValues()
    {
        return $this->hasMany(FormValue::class, 'form_id');
    }

    public function currentUserFormValues()
    {
        return $this->formValues()->user(auth()->user()->id);
    }
}
