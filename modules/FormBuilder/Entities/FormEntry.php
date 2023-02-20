<?php

namespace Modules\FormBuilder\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kodeine\Metable\Metable;

class FormEntry extends Model
{
    use HasFactory;
    use Metable;

    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FormEntryFactory::new();
    }

    public function saveFromInputs(array $inputs): void
    {
        foreach ($inputs as $key => $value) {
            $this->$key = $value;
        }

        $this->save();
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->withTrashed();
    }

    private function isFileUpload(mixed $value): bool
    {
        if (
            is_array($value)
            && isset($value['files'])
        ) {
            return true;
        }

        return false;
    }
}
