<?php

namespace App\View\Components\Builder\Content;

use Illuminate\Support\Collection;

class Carousel extends BaseContent
{
    public $carouselImages = [];
    public $config;
    public $images;
    public $slideSpeed = 6000;

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
        $this->carouselImages = $this->getCarouselImages();
        $this->config = $this->getConfig()['carousel'];
    }

    private function getCarouselImages(): array
    {
        $mediaIds = $this->entity['content']['carousel']['images'];
        $mediaIds = collect($mediaIds)->pluck('mediaId')->all();
        $carouselImages = [];
        foreach ($mediaIds as $mediaId) {
            if ($mediaId && $this->images) {
                $image = $this->images->firstWhere('id', $mediaId);
                $image->append('optimizedImageUrl');

                $carouselImages[] = $image;
            }
        }

        return $carouselImages;
    }
}
