<?php

namespace App\View\Components\Builder\Content;

use App\View\Components\Builder\Content\Heading;

class Faq extends Heading
{
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

        $this->headingContent = $this->contentHtml();

        $this->faqContents = $this->getFaqContents();
    }

    private function getFaqContents(): array
    {
        return $this->entity['content']['faqContent']['contents'] ?? [];
    }
}
