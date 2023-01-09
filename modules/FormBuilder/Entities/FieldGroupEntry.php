<?php

namespace Modules\FormBuilder\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kodeine\Metable\Metable;

class FieldGroupEntry extends Model
{
    use HasFactory;
    use Metable;

    protected static function newFactory()
    {
        return \Modules\FormBuilder\Database\factories\FieldGroupEntryFactory::new();
    }

    public function saveFromInputs(array $inputs): void
    {
        foreach ($inputs as $key => $value) {
            $this->$key = $value;
        }

        $this->save();
    }

    public function fieldGroup()
    {
        return $this->belongsTo(FieldGroup::class, 'field_group_id');
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
