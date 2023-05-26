<?php

namespace App\View\Components\Builder\Content;

class Button extends BaseContent
{
    public $buttonClasses = [];
    public $buttonContent = [];
    public $link = null;
    public $target = null;
    public $isDownload = false;
    public $textClasses = [];
    public $icon = [];

    private $config = [];
    private $visibility = [];

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
        $this->textClasses = $this->getTextClasses();
        $this->wrapperClasses[] = $this->config['position'] ?? null;

        $this->buttonContent = $this->getButtonContent();
        $this->isDownload = $this->config['target'] === 'download';
        $this->link = $this->config['link'] ?? null;
        $this->target = $this->setTarget();

        $this->icon = $this->getButtonIcon();

        $this->visibility = $this->getButtonVisibility();
    }

    private function getButtonConfig(): array
    {
        return $this->getConfig()['button'] ?? [];
    }

    private function getButtonVisibility(): array
    {
        return $this->getConfig()['visibility'] ?? [];
    }

    private function getButtonClasses(): array
    {
        $classes = collect();

        $classes->push($this->config['color'] ?? null);
        $classes->push($this->config['isLight'] ? 'is-light' : '');
        $classes->push($this->config['size'] ?? null);
        $classes->push($this->config['width'] ?? null);
        $classes->push($this->config['style'] ?? null);
        $classes->push($this->visibility['device'] ?? null);

        return $classes->filter()->values()->all();
    }

    private function getTextClasses(): array
    {
        $classes = collect();

        $classes->push($this->config['textWeight'] ?? null);

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

    public function getButtonIcon(): array
    {
        return $this->getConfig()['icon'];
    }
}
