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
        $textConfig = $this->entity['config']['text'] ?? [];
        $classes = collect();

        $classes->push($textConfig['size'] ?? null);
        $classes->push($textConfig['alignment'] ?? null);

        return $classes->filter()->all();
    }
}
