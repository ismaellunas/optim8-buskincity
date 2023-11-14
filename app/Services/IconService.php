<?php

namespace App\Services;

use App\Traits\HasCache;
use Illuminate\Support\Facades\File;

class IconService
{
    use HasCache;

    private function getIcons(): array
    {
        $key = 'icons';

        if (! $this->hasLoadedKey($key)) {
            $this->setLoadedKey(
                'icons',
                json_decode(File::get(base_path('resources/js/Json/icon-list.json')), true) ?? []
            );
        }

        return $this->getLoadedKey($key);
    }

    public function getClasses(string $key): string
    {
        $iconStyle = config('constants.icon.type');

        if (config('constants.fontawesome_free')) {
            $iconStyle = 'fa-solid';
        }

        $icons = $this->getIcons()[$key] ?? [];
        $icon = $icons[0] ?? '';

        if (! config('constants.fontawesome_free')) {
            $icon = last($icons) ?? '';
        }

        return $iconStyle .' '. $icon;
    }
}
