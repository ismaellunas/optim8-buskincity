<?php

namespace App\View\Components\Builder\Content;

use Mews\Purifier\Facades\Purifier;

class CardText extends BaseContent
{
    public $cardClasses = [];
    public $cardContentClasses = [];

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->cardClasses = $this->getCardClasses();
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

    public function getCardClasses(): array
    {
        $configCard = $this->getConfig()['card'] ?? [];
        $classes = collect();

        $classes->push('card');
        $classes->push($configCard['rounded'] ?? null);
        $classes->push($configCard['isShadowless'] ? 'is-shadowless' : '');

        return $classes->filter()->all();
    }
}
