<?php

namespace App\View\Components\Builder\Content;

class Text extends BaseContent
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

        if ($entity['config']) {
            $this->classes = $entity['config']['text'] ?? [];
        }
    }
}
