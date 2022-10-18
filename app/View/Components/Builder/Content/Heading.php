<?php

namespace App\View\Components\Builder\Content;

use Mews\Purifier\Facades\Purifier;

class Heading extends BaseContent
{
    public $headingClasses = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->headingClasses = $this->getHeadingClasses();
    }

    private function getHeadingConfig(): array
    {
        return $this->getConfig()['heading'] ?? [];
    }

    private function getHeadingClasses(): array
    {
        $configHeading = $this->getHeadingConfig();

        $classes = collect();
        $classes->push($configHeading['type'] ?? null);
        $classes->push($configHeading['alignment'] ?? null);
        $classes->push('is-'.substr($this->headingTag(), -1));

        return $classes->filter()->all();
    }

    public function headingTag(): string
    {
        return $this->getHeadingConfig()['tag'] ?? 'h1';
    }

    public function contentHtml(): string
    {
        $dirtyHtml = $this->entity['content']['heading']['html'] ?? '';

        if (!empty($dirtyHtml)) {
            return Purifier::clean($dirtyHtml, 'tinymce');
        }

        return '';
    }
}
