<?php

namespace App\View\Components\Builder\Content;

use Illuminate\Support\Collection;
use App\Models\Media;
use Mews\Purifier\Facades\Purifier;

class Card extends BaseContent
{
    public $locale;
    public $images;
    public $cardLink;

    public $imageMedia = null;
    public $hasImage = false;

    public $cardClasses = [];
    public $cardContentClasses = [];
    public $cardImageClasses = [];
    public $imageStyles = [];
    public $position = null;

    public function __construct(
        $entity,
        string $locale = null,
        Collection $images = null
    ) {
        parent::__construct($entity);

        $this->images = $images;
        $this->locale = $locale;
        $this->cardLink = $this->getCardLink();

        $this->cardClasses = $this->getCardClasses();
        $this->cardContentClasses = $this->getCardContentClasses();
        $this->cardImageClasses = $this->getCardImageClasses();
        $this->imageMedia = $this->getImageMedia();
        $this->imageStyles = $this->getImageStyles();
        $this->hasImage = !empty($this->imageMedia);

        $this->position = $this->getPosition();
    }

    public function contentHtml(): string
    {
        $dirtyHtml = $this->entity['content']['cardContent']['content']['html'] ?? '';

        if (!empty($dirtyHtml)) {
            return Purifier::clean($dirtyHtml, 'tinymce');
        }

        return '';
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

    private function getPosition(): string
    {
        return $this->getConfig()['image']['position'] ?? '';
    }

    public function hasPosition(): bool
    {
        return !empty($this->position);
    }

    public function getCardClasses(): array
    {
        $configCard = $this->getConfig()['card'] ?? [];
        $classes = collect();

        $classes->push('card');
        $classes->push($configCard['rounded'] ?? null);
        $classes->push($configCard['isShadowless'] ? 'is-shadowless' : '');

        return $classes->filter()->all();
    }

    private function getCardLink(): ?string
    {
        return $this->getConfig()['card']['link'] ?? null;
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
        $configContent = $this->getConfig()['content'] ?? [];
        $classes = collect();

        $classes->push($configContent['size'] ?? null);
        $classes->push($configContent['alignment'] ?? null);

        return $classes->filter()->all();
    }

    private function getCardImageClasses(): array
    {
        $configImage = $this->getConfig()['image'] ?? [];
        $classes = collect();

        if ($configImage['padding']) {
            $classes->push($this
                ->pageBuilderService
                ->createPaddingClasses($configImage['padding'])
            );
        }

        return $classes->flatten()->all();
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
