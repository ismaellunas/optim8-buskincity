<?php

namespace App\View\Components\Builder\Content;

class Text extends BaseContent
{
    public $classes = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->classes = $this->getClasses();
    }

    private function getClasses(): array
    {
        $textConfig = $this->getConfig()['text'] ?? [];
        $classes = collect();

        $classes->push($textConfig['size'] ?? null);
        $classes->push($textConfig['alignment'] ?? null);
        $classes->push($textConfig['color'] ?? null);

        return $classes->filter()->all();
    }
}
