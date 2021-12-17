<?php

namespace App\View\Components\Builder\Content;

class CardText extends BaseContent
{
    public $cardContentClasses = [];

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->cardContentClasses = $this->getCardContentClasses();
    }

    private function getCardContentClasses(): array
    {
        $configContent = $this->entity['config']['content'] ?? [];
        $classes = collect();

        $classes->push($configContent['size'] ?? null);
        $classes->push($configContent['alignment'] ?? null);

        return $classes->filter()->all();
    }

    public function contentHtml(): string
    {
        return $this->entity['content']['cardContent']['content']['html'] ?? '';
    }
}
