<?php

namespace App\Services;

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
}
