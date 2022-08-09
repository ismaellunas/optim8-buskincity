<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface SitemapInterface
{
    public function urls(): array|Collection;

    public function locTag(): string;

    public function optionalTags(): array;
}