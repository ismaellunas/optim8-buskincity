<?php

namespace App\View\Components\Builder\Content;

class Button extends BaseContent
{
    public $buttonClasses = [];
    public $buttonContent = [];
    public $iconPosition = null;
    public $link = null;
    public $target = null;
    public $isDownload = false;

    private $config = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->config = $this->getButtonConfig();
        $this->buttonClasses = $this->getButtonClasses();
        $this->buttonContent = $this->getButtonContent();
        $this->iconPosition = $this->config['iconPosition'] ?? null;
        $this->link = $this->config['link'] ?? null;
        $this->target = $this->setTarget();
        $this->isDownload = $this->config['target'] === 'download';
    }

    private function getButtonConfig(): array
    {
        return $this->entity['config']['button'] ?? [];
    }

    private function getButtonClasses(): array
    {
        $classes = collect();

        $classes->push($this->config['color'] ?? null);
        $classes->push($this->config['isLight'] ? 'is-light' : '');
        $classes->push($this->config['size'] ?? null);
        $classes->push($this->config['width'] ?? null);
        $classes->push($this->config['style'] ?? null);

        return $classes->filter()->values()->all();
    }

    private function getButtonContent(): array
    {
        return $this->entity['content']['button'] ?? [];
    }

    private function setTarget(): ?string
    {
        return $this->config['target'] !== 'download' ? $this->config['target'] : null;
    }
}
