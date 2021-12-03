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

    public $imageClasses = [];
    public $figureClasses = [];

    private $_alt;
    private $_src;
    private $media;

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
        string $src = null,
        string $locale = null,
        $media = null
    ) {
        $this->_alt = $alt;
        $this->_src = $src;

        $this->rounded = $rounded;
        $this->ratio = $ratio;
        $this->rounded = $rounded;
        $this->square = $square;
        $this->media = $media;
        $this->locale = $locale;

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
            return $this->media->translate($this->locale, true)->alt;
        }
        return null;
    }

    public function src(): ?string
    {
        if ($this->_src) {
            return $this->_src;
        } elseif ($this->media) {
            return $this->media->file_url;
        }
        return null;
    }

    public function getStyle($style): ?string
    {
        if (is_array($style)) {
            return implode(' ', $style);
        }
        return $style ?? null;
    }

    private function getImageClasses(): array
    {
        $classes = collect();

        $classes->push($this->rounded);

        return $classes->filter()->all();
    }

    private function getFigureClasses(): array
    {
        $classes = collect();

        $classes->push($this->ratio);
        $classes->push($this->square);

        return $classes->filter()->all();
    }
}
