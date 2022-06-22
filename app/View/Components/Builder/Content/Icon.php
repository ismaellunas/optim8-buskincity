<?php

namespace App\View\Components\Builder\Content;

class Icon extends BaseContent
{
    public $config = [];
    public $iconStyle = null;
    public $uid = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->config = $this->getIconConfig();
        $this->uid = $entity['id'];
    }

    private function getIconConfig(): array
    {
        return $this->entity['config']['icon'] ?? [];
    }
}
