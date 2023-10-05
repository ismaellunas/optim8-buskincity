<?php

namespace App\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum PublishingStatus: int
{
    case DRAFT = 0;
    case PUBLISHED = 1;

    public static function options(): Collection
    {
        return collect(self::cases())
            ->map(fn ($option) => [
                'id' => $option->value,
                'value' => Str::title($option->name)
            ]);
    }
}
