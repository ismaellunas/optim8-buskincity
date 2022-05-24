<?php

namespace App\Traits;

trait HasLocale
{
    public function scopeInLanguages($query, array $locales)
    {
        return $query->whereIn('locale', $locales);
    }
}
