<?php

namespace App\Entities;

use Illuminate\Support\Collection;

class StyleBlock
{
    public $selector;
    public Collection $styles;

    public function __construct(string $selector, Collection $styles = null)
    {
        $this->selector = $selector;
        $this->styles = $styles ?? collect();
    }

    public function isEmpty(): bool
    {
        return $this->styles->isEmpty();
    }

    public function addStyle(string $style, string $value)
    {
        $this->styles->put($style, $value);
    }

    private function stylesText(): string
    {
        $styles = [];
        $template = ":style : :value";

        foreach ($this->styles as $style => $value) {
            $styles[] = preg_replace_array('/:[a-z_]+/', [$style, $value], $template);
        }

        return implode('; ', $styles);
    }

    public function toText(): string
    {
        $template = ":selector {:styles}";

        return preg_replace_array(
            '/:[a-z_]+/',
            [$this->selector, $this->stylesText()],
            $template
        );
    }
}
