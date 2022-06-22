<?php

namespace App\View\Components\Builder\Content;

class Icon extends BaseContent
{
    public $iconStyle = null;
    public $config = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->config = $this->getIconConfig();
        $this->iconStyle = $this->getIconStyle();
    }

    private function getIconConfig(): array
    {
        return $this->entity['config']['icon'] ?? [];
    }

    private function getIconStyle(): ?string
    {
        return $this->config['size']
            ? 'font-size: ' . $this->config['size'] . 'px; height: auto; width: auto'
            : null;
    }
}
