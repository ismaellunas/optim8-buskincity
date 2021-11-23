<?php

namespace App\View\Components\Builder\Content;

class Heading extends BaseContent
{
    public $headingClasses = [];
    public $headingTag = 'h1';
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->headingClasses = $this->getHeadingClasses();

        $this->headingTag = $this->getHeadingTag();
    }

    private function getHeadingClasses(): array
    {
        $configHeading = $this->entity['config']['heading'] ?? [];
        $classes = collect();
        $classes->push($configHeading['type'] ?? null);
        $classes->push($configHeading['alignment'] ?? null);

        return $classes->filter()->all();
    }

    private function getHeadingTag(): string
    {
        return $this->entity['config']['heading']['tag'] ?? 'h1';
    }

    public function contentHtml(): string
    {
        return $this->entity['content']['heading']['html'] ?? '';
    }
}
