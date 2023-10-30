<?php

namespace App\Entities;

use App\Traits\HasCache;
use Illuminate\Translation\Translator as IlluminateTranslator;

class Translator extends IlluminateTranslator
{
    use HasCache;

    private function moduleTermReplace(string $key, $locale = null): string
    {
        $cacheKey = 'term_replace::'.($locale ?? '-').'::'.$key;

        return $this->staticRemember(
            $cacheKey,
            fn () => (string) parent::get($key, [], $locale)
        );
    }

    private function moduleTermReplaces($translated, $locale = null): array
    {
        $replaces = [];

        if (preg_match_all(
            '/(?<=:)[\w.\-]+(?:_term|_TERM|_Term)\.[\w\-]+/',
            $translated,
            $replaceKeys
        )) {
            foreach ($replaceKeys[0] as $replaceKey) {
                $key = strtolower($replaceKey);
                $replaces[$key] = $this->moduleTermReplace($key, $locale);
            }
        }

        return $replaces;
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $trans = parent::get($key, $replace, $locale, $fallback);

        if (!is_array($trans)) {

            $newReplaces = $this->moduleTermReplaces($trans, $locale);

            if (!empty($newReplaces)) {
                return parent::get($trans, $newReplaces, $locale);
            }

        } else {

            foreach ($trans as $key => $value) {

                if (! is_string($value)) {
                    continue;
                }

                $newReplaces = $this->moduleTermReplaces($value, $locale);

                if (!empty($newReplaces)) {
                    $trans[$key] = parent::get($value, $newReplaces, $locale);
                }
            }
        }

        return $trans;
    }
}
