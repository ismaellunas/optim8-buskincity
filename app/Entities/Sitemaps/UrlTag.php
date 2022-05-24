<?php

namespace App\Entities\Sitemaps;

use Carbon\CarbonInterface;

class UrlTag
{
    public $loc;
    public ?CarbonInterface $lastmod;

    public function __construct(string $loc, array $optionalAttributes = [])
    {
        $this->loc = $loc;
        $this->lastmod = $optionalAttributes['lastmod'] ?? null;
    }
}
