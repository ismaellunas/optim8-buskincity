<?php

namespace App\View\Components\Builder\Content;

use App\Models\Media;
use Illuminate\Support\Collection;

class Image extends BaseContent
{

    public $images;
    public $imageMedia = null;
    public $hasImage = false;
    public $imageClasses = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $entity,
        Collection $images = null
    ) {
        parent::__construct($entity);

        $this->images = $images;

        $this->imageMedia = $this->getImageMedia();
    }

    public function ratio(): string
    {
        return $this->entity['config']['image']['ratio'] ?? '';
    }

    public function rounded(): string
    {
        return $this->entity['config']['image']['rounded'] ?? '';
    }

    public function fixedSquare(): string
    {
        return $this->entity['config']['image']['fixedSquare'] ?? '';
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
}
