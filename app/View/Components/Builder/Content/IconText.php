<?php

namespace App\View\Components\Builder\Content;

class IconText extends BaseContent
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

    public function entityContentText(): string
    {
        return $this->entity['content']['text'] ?? '';
    }

    public function iconClass(): string
    {
        return $this->getConfig()['icon']['class'] ?? '';
    }

    private function getClasses(): array
    {
        $textConfig = $this->getConfig()['text'] ?? [];
        $classes = collect();

        $classes->push($textConfig['size'] ?? null);
        $classes->push($textConfig['alignment'] ?? null);
        $classes->push($textConfig['color'] ?? null);
        $classes->push($textConfig['weight'] ?? null);

        return $classes->filter()->all();
    }
}
