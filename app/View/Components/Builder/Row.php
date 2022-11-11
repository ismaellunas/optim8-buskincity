<?php

namespace App\View\Components\Builder;

use Illuminate\Support\Str;
use Illuminate\View\Component;

class Row extends Component
{
    public $uid;
    public $backgroundColor;
    public $columns;
    public $customId;
    public $entities;
    public $hasBackgroundImage;
    public $images;
    public $isFullwidth;
    public $isSectionIncluded;
    public $locale;
    public $rounded;
    public $sectionSize;
    public $uniqueClass;
    public $sizeColumns;

    private $config;

    public function __construct($uid, $columns, $entities, $locale, $images = [])
    {
        $this->uid = $uid;
        $this->columns = $columns;
        $this->entities = $entities;
        $this->locale = $locale;
        $this->images = $images;
        $this->uniqueClass = $this->getUniqueClass();

        $this->config = $entities[$this->uid]['config'];
        $this->isSectionIncluded = $this->isSectionUsed();
        $this->hasBackgroundImage = $this->hasBackgroundImage();

        $configWrapper = $this->config['wrapper'] ?? null;
        if ($configWrapper) {
            $this->customId = $configWrapper['customId'] ?? null;
            $this->backgroundColor = $configWrapper['backgroundColor'] ?? '';
            $this->isFullwidth = $configWrapper['isFullwidth'] ?? false;
            $this->rounded = $configWrapper['rounded'] ?? null;
        }

        $this->sizeColumns = $this->config['columns'] ?? [];

        if ($this->isSectionIncluded) {
            $this->sectionSize = $this->config['section']['size'] ?? null;
        }
    }

    private function isSectionUsed(): bool
    {
        return $this->config['section']['isIncluded'] ?? false;
    }

    private function hasBackgroundImage(): bool
    {
        return !empty($this->config['wrapper']['backgroundImage']) ?? false;
    }

    private function getUniqueClass(): string
    {
        return 'pb-'.Str::lower($this->uid);
    }

    public function render()
    {
        return view('components.builder.row');
    }
}
