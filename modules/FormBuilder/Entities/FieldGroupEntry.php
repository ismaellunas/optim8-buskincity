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

    public function saveFromInputs($inputs): void
    {
        foreach ($inputs as $key => $value) {
            $this->$key = $value;
        }

        $this->save();
    }
}
