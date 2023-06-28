<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FigureImage extends Component
{
    public $style;
    public $figureClasses = [];

    public $src;
    public $alt;
    public $rounded;
    public $imgStyle;
    public $locale;
    public $media;
    public $isLazyload;

    private $ratio;
    private $square;
    private $hasPosition;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        mixed $style = null,
        mixed $imgStyle = null,
        string $src = null,
        string $alt = null,
        string $ratio = null,
        string $rounded = null,
        string $square = null,
        string $hasPosition = null,
        string $locale = null,
        bool $isLazyload = false,
        $media = null
    ) {
        $this->ratio = $ratio;
        $this->square = $square;
        $this->hasPosition = $hasPosition;

        $this->alt = $alt;
        $this->src = $src;
        $this->rounded = $rounded;
        $this->media = $media;
        $this->locale = $locale;
        $this->isLazyload = $isLazyload;;
        $this->imgStyle = $imgStyle;

        $this->style = $this->getStyle($style);

        $this->figureClasses = $this->getFigureClasses();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.figure-image');
    }

    public function getStyle($style): ?string
    {
        if (is_array($style)) {
            return implode(';', $style);
        }
        return $style ?? null;
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
