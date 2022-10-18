<?php

namespace App\View\Components\Builder\Content;

use App\View\Components\Builder\Content\Heading;
use Mews\Purifier\Facades\Purifier;

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
        $faqContents = [];
        $contents = $this->entity['content']['faqContent']['contents'] ?? [];

        foreach ($contents as $content) {
            $faqContents[] = [
                'question' => $content['question'],
                'answer' => Purifier::clean($content['answer'], 'tinymce'),
            ];
        }

        return $faqContents;
    }
}
