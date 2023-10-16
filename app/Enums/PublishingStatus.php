<?php

namespace App\Enums;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

enum PublishingStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public static function options(): Collection
    {
        return collect(self::cases())->map(fn ($option) => [
            'id' => $option->value,
            'value' => Str::title($option->name)
        ]);
    }
}
