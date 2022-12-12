<?php

namespace App\View\Components\Builder\Content;

use App\Models\Media;
use Illuminate\Support\Collection;

class Image extends BaseContent
{
    public $locale;
    public $images;
    public $imageMedia = null;
    public $hasImage = false;
    public $imageClasses = [];
    public $imageStyles = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $entity,
        string $locale = null,
        Collection $images = null
    ) {
        parent::__construct($entity);

        $this->images = $images;

        $this->locale = $locale;

        $this->imageMedia = $this->getImageMedia();
        $this->imageStyles = $this->getImageStyles();
    }

    public function ratio(): string
    {
        return $this->getConfig()['image']['ratio'] ?? '';
    }

    public function rounded(): string
    {
        return $this->getConfig()['image']['rounded'] ?? '';
    }

    public function fixedSquare(): string
    {
        return $this->getConfig()['image']['fixedSquare'] ?? '';
    }

    public function position(): string
    {
        return $this->getConfig()['image']['position'] ?? '';
    }

    private function getImageMedia(): ?Media
    {
        if (!is_null($this->imageMedia)) {
            return $this->imageMedia;
        }

        $mediaId = $this->entity['content']['figure']['image']['mediaId'];

        if ($mediaId && $this->images) {
            $this->imageMedia = $this->images->firstWhere('id', $mediaId);
        }

        return $this->imageMedia;
    }

    private function getImageStyles(): array
    {
        $configImage = $this->getConfig()['image'];

        $width = !empty($configImage['width']) && $configImage['width'] != ""
            ? 'width: '.$configImage['width'].'px'
            : null;
        $height = !empty($configImage['height']) && $configImage['height'] != ""
            ? 'height: '.$configImage['height'].'px'
            : null;

        $classes = collect();

        $classes->push($width);
        $classes->push($height);

        return $classes->filter()->all();
    }
}
