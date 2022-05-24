<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel implements TranslatableContract
{
    use HasFactory;
    use Translatable;

    public $translatedAttributes = ['name'];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

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

    /* Accessors: */
    public function getFirstTranslationNameAttribute(): string
    {
        return $this->name ?? $this->translations[0]->name;
    }
}
