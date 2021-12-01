<?php

namespace App\View\Components\Builder\Content;

use App\Models\Media;
use Illuminate\Support\Collection;

class Carousel extends BaseContent
{
    public $locale;
    public $images;
    public $carouselImages = [];
    public $numberOfSliders = 0;
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
        $this->carouselImages = $this->getCarouselImages();
        $this->numberOfSliders = $this->numberOfSliders();
    }

    public function ratio(): string
    {
        return $this->entity['config']['carousel']['ratio'] ?? '';
    }

    public function autoPlay(): bool
    {
        return $this->entity['config']['carousel']['autoPlay'] ?? false;
    }

    private function numberOfSliders(): string
    {
        return $this->entity['config']['carousel']['numberOfSliders'] ?? '';
    }

    private function getCarouselImages(): array
    {
        $mediaIds = $this->entity['content']['carousel']['images'];
        $mediaIds = collect($mediaIds);
        $mediaIds = $mediaIds->pluck('mediaId')->all();
        $carouselImages = [];
        foreach($mediaIds as $mediaId) {
            if ($mediaId && $this->images) {
                $carouselImages[] = $this->images->firstWhere('id', $mediaId);
            }
        }

        $this->carouselImages = $carouselImages;

        return $this->carouselImages;
    }
}
