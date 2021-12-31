<?php

namespace App\View\Components\Builder\Content;

class Tabs extends BaseContent
{
    public $classes = [];
    public $tabsContent = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->classes = $this->getClasses();
        $this->tabsContent = $this->getTabsContent();
    }

    private function getClasses(): array
    {
        $tabsConfig = $this->entity['config']['tabs'] ?? [];
        $classes = collect();

        $classes->push($tabsConfig['alignment'] ?? null);
        $classes->push($tabsConfig['size'] ?? null);
        $classes->push($tabsConfig['style'] ?? null);
        $classes->push($tabsConfig['width'] ?? null);

        return $classes->filter()->values()->all();
    }

    private function getTabsContent(): array
    {
        return $this->entity['content']['tabs'] ?? [];
    }
}
