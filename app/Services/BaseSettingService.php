<?php

namespace App\Services;

use App\Entities\Caches\SettingCache;
use App\Models\Setting;
use App\Traits\HasCache;

abstract class BaseSettingService
{
    use HasCache;

    protected static function getKey(string $key): string
    {
        return app(SettingCache::class)->remember($key, function () use ($key) {
            return Setting::key($key)->value('value') ?? "";
        });
    }

    public function getArrayValueByKey(string $key): array
    {
        $value = $this->getKey($key);

        return is_null($value) || $value == ""
            ? []
            : json_decode($value, TRUE);
    }
}