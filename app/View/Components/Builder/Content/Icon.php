<?php

namespace App\View\Components\Builder\Content;

class Icon extends BaseContent
{
    public $config = [];
    public $iconStyle = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->config = $this->getIconConfig();
    }

    private function getIconConfig(): array
    {
        return $this->getConfig()['icon'] ?? [];
    }
}
