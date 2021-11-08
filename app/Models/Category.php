<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel implements TranslatableContract
{
    use HasFactory;
    use Translatable;

    public $translatedAttributes = ['name', 'slug'];

    public function saveFromInputs(array $inputs): bool
    {
        $this->fill($inputs);
        return $this->save();
    }

    public function syncTranslations(array $providedLocales)
    {
        $existingLocales = array_keys($this->getTranslationsArray());

        $unusedLocales = array_diff($existingLocales, $providedLocales);

        if (!empty($unusedLocales)) {
            $this->deleteTranslations($unusedLocales);
        }
    }
}
