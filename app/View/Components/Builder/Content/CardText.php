<?php

namespace App\View\Components\Builder\Content;

use Mews\Purifier\Facades\Purifier;

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
        $configContent = $this->getConfig()['content'] ?? [];
        $classes = collect();

        $classes->push($configContent['size'] ?? null);
        $classes->push($configContent['alignment'] ?? null);

        return $classes->filter()->all();
    }

    public function contentHtml(): string
    {
        $dirtyHtml = $this->entity['content']['cardContent']['content']['html'] ?? '';

        if (!empty($dirtyHtml)) {
            return Purifier::clean($dirtyHtml, 'tinymce');
        }

        return '';
    }

    public function cardRounded(): string
    {
        return $this->getConfig()['card']['rounded'] ?? '';
    }
}
