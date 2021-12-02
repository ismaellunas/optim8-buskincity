<?php

namespace App\View\Components\Builder\Content;

use Illuminate\Support\Collection;
use App\Models\Media;

class Card extends BaseContent
{
    public $locale;
    public $images;

    public $imageMedia = null;
    public $hasImage = false;

    public $cardContentClasses = [];
    public $cardImageClasses = [];

    public function __construct(
        $entity,
        string $locale = null,
        Collection $images = null
    ) {
        parent::__construct($entity);

        $this->images = $images;
        $this->locale = $locale;

        $this->cardContentClasses = $this->getCardContentClasses();
        $this->cardImageClasses = $this->getCardImageClasses();
        $this->imageMedia = $this->getImageMedia();
        $this->hasImage = !empty($this->imageMedia);
    }

    public function contentHtml(): string
    {
        return $this->entity['content']['cardContent']['content']['html'] ?? '';
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

        $mediaId = $this->entity['content']['cardImage']['figure']['image']['mediaId'];

        if ($mediaId && $this->images) {
            $this->imageMedia = $this->images->firstWhere('id', $mediaId);
        }

        return $this->imageMedia;
    }

    private function getCardContentClasses(): array
    {
        $configContent = $this->entity['config']['content'] ?? [];
        $classes = collect();

        $classes->push($configContent['size'] ?? null);
        $classes->push($configContent['alignment'] ?? null);

        return $classes->filter()->all();
    }

    private function getCardImageClasses(): array
    {
        $configImage = $this->entity['config']['image'] ?? [];
        $classes = collect();

        if ($configImage['padding']) {
            $classes->push($this
                ->pageBuilderService
                ->createPaddingClasses($configImage['padding'])
            );
        }

        return $classes->flatten()->all();
    }
}
