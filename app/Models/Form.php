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

    public function scopeUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeName($query, string $name)
    {
        return $query->where('name', $name);
    }

    public function getLatestUserValue(int $userId): ?FormValue
    {
        return $this->formValues->last(function ($formValue) use ($userId) {
            return $formValue->user_id == $userId;
        });
    }

}
