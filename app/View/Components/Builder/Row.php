<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Row extends Component
{
    public $uid;
    public $columns;
    public $entities;
    public $locale;
    public $images;
    public $isFullwidth;
    public $backgroundColor;
    public $isSectionIncluded;
    public $sectionSize;

    private $config;

    public function __construct($uid, $columns, $entities, $locale, $images = [])
    {
        $this->uid = $uid;
        $this->columns = $columns;
        $this->entities = $entities;
        $this->locale = $locale;
        $this->images = $images;

        $this->config = $entities[$this->uid]['config'];
        $this->isSectionIncluded = $this->isSectionUsed();

        $configWrapper = $this->config['wrapper'] ?? null;
        if ($configWrapper) {
            $this->backgroundColor = $this->config['wrapper']['backgroundColor'] ?? '';
            $this->isFullwidth = $this->config['wrapper']['isFullwidth'] ?? false;
        }

        if ($this->isSectionIncluded) {
            $this->sectionSize = $this->config['section']['size'] ?? null;
        }
    }

    private function isSectionUsed(): bool
    {
        return $this->config['section']['isIncluded'] ?? false;
    }

    public function render()
    {
        return view('components.builder.row');
    }
}
