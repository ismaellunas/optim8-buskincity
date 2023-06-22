<?php

namespace App\View\Components;

use App\Models\Media;
use Illuminate\View\Component;

class Image extends Component
{
    public $ratio;
    public $rounded;
    public $square;
    public $style;
    public $locale;
    public $isLazyload;

    public $imageClasses = [];
    public $figureClasses = [];

    private $_alt;
    private $_src;
    private $media;
    private $hasPosition;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        mixed $style = null,
        string $alt = null,
        string $ratio = null,
        string $rounded = null,
        string $square = null,
        string $hasPosition = null,
        string $src = null,
        string $locale = null,
        bool $isLazyload = false,
        $media = null
    ) {
        $this->_alt = $alt;
        $this->_src = $src;

        $this->rounded = $rounded;
        $this->ratio = $ratio;
        $this->rounded = $rounded;
        $this->square = $square;
        $this->hasPosition = $hasPosition;
        $this->media = $media;
        $this->locale = $locale;
        $this->isLazyload = $isLazyload;;

        $this->style = $this->getStyle($style);
        $this->imageClasses = $this->getImageClasses();
        $this->figureClasses = $this->getFigureClasses();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.image');
    }

    public function alt(): ?string
    {
        if (!empty($this->_alt)) {

            return $this->_alt;

        } elseif (!empty($this->media)) {

            $translation = $this->media->translate($this->locale, true);

            if (!empty($translation)) {
                return $this->media->translate($this->locale, true)->alt;
            }
        }

        return null;
    }

    public function src(): ?string
    {
        if ($this->_src) {
            return $this->_src;
        } elseif ($this->media) {
            return $this->media->optimizedImageUrl;
        }
        return null;
    }

    public function getStyle($style): ?string
    {
        if (is_array($style)) {
            return implode(';', $style);
        }
        return $style ?? null;
    }

    private function getImageClasses(): array
    {
        $classes = collect();

        $classes->push($this->rounded);

        if ($this->isLazyload) {
            $classes->push('lazyload');
        }

        return $classes->filter()->all();
    }

    private function getFigureClasses(): array
    {
        $classes = collect();

        $classes->push($this->ratio);
        $classes->push($this->square);

        if ($this->hasPosition && !$this->ratio) {
            $classes->push('is-inline-block');
        }

        return $classes->filter()->all();
    }
}
