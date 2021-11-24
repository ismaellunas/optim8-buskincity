<?php

namespace App\View\Components\Builder\Content;

use App\View\Components\Builder\Content\Heading;

class Faq extends BaseContent
{
    public $headingClasses = [];
    public $headingTag = 'h1';
    public $headingContent = '';
    public $faqContents = [];
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $heading = new Heading($entity);
        $this->headingClasses = $heading->headingClasses;
        $this->headingTag = $heading->headingTag;
        $this->headingContent = $heading->contentHtml();

        $this->faqContents = $this->getFaqContents();
    }

    private function getFaqContents(): array
    {
        return $this->entity['content']['faqContent']['contents'] ?? [];
    }
}
