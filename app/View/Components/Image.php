<?php

namespace App\View\Components;

use App\Models\Media;
use Illuminate\View\Component;

class Image extends Component
{
    public $style;
    public $isLazyload;

    public $imageClasses = [];

    private $_alt;
    private $_src;
    private $_width;
    private $_height;
    private $rounded;
    private $locale;
    private $media;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        mixed $style = null,
        string $src = null,
        string $alt = null,
        string $width = null,
        string $height = null,
        string $rounded = null,
        string $locale = null,
        bool $isLazyload = false,
        $media = null
    ) {
        $this->_alt = $alt;
        $this->_src = $src;
        $this->_width = $width;
        $this->_height = $height;

        $this->rounded = $rounded;
        $this->media = $media;
        $this->locale = $locale;
        $this->isLazyload = $isLazyload;;

        $this->style = $this->getStyle($style);
        $this->imageClasses = $this->getImageClasses();
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

    public function width(): mixed
    {
        if ($this->_width) {
            return $this->_width;
        } elseif ($this->media) {
            return $this->media['width'] ?? null;
        }
        return null;
    }

    public function height(): mixed
    {
        if ($this->_height) {
            return $this->_height;
        } elseif ($this->media) {
            return $this->media['height'] ?? null;
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
}
