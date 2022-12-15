<?php

namespace App\Services;

use App\Models\PageTranslation;
use Illuminate\Support\Collection;

class PageBuilderService
{
    private function createTrblClasses(array $trbl, string $prefix = null): array
    {
        $classes = [];

        $suffix = [
            'top' => 't',
            'right' => 'r',
            'bottom' => 'b',
            'left' => 'l'
        ];

        foreach ($trbl as $key => $value) {
            if (!empty($value)) {
                $classes[] = $prefix . $suffix[$key] . '-' . $value;
            }
        }

        return $classes;
    }

    public function createPaddingClasses(array $trbl): array
    {
        return $this->createTrblClasses($trbl, 'p');
    }

    public function createMarginClasses(array $trbl): array
    {
        return $this->createTrblClasses($trbl, 'm');
    }

    public function userListOrderOptions(): Collection
    {
        return collect([
            ['id' => "first_name-asc", 'value' => "A-Z"],
            ['id' => "first_name-desc", 'value' => "Z-A"],
            ['id' => "random", 'value' => "Random"],
            ['id' => "created_at-asc", 'value' => "Date"],
        ]);
    }

    public static function backgroundColors(): array
    {
        return [
            'has-background-white',
            'has-background-black',
            'has-background-light',
            'has-background-dark',
            'has-background-primary',
            'has-background-link',
            'has-background-info',
            'has-background-success',
            'has-background-warning',
            'has-background-danger',
        ];
    }

    public function bodyClasses(PageTranslation $pageTranslation): array
    {
        return [
            $pageTranslation->getSettingValueByKey('main_background_color')
        ];
    }

    public function bodyStyles(PageTranslation $pageTranslation): string
    {
        $styles = '';
        $pageHeight = $pageTranslation->getSettingValueByKey('height');

        if ($pageHeight) {
            $styles = 'height: '.$pageHeight.'vh;';
        }

        return $styles;
    }
}
