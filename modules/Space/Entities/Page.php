<?php

namespace Modules\Space\Entities;

use App\Contracts\PublishableInterface;
use App\Models\Page as ModelPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Space\Entities\PageTranslation;
use Modules\Space\Entities\Space;

class Page extends ModelPage
{
    protected $translationModel = PageTranslation::class;

    public const TYPE = 'space';

    protected $attributes = [
        'type' => self::TYPE,
    ];

    protected static function newFactory()
    {
        return \Modules\Space\Database\factories\PageFactory::new();
    }

    protected function customNewQuery($newQuery): Builder
    {
        return $newQuery->type(self::TYPE);
    }

    public function space()
    {
        return $this->hasOne(Space::class);
    }

    public static function createBasedOnSpace(string $title, ?int $authorId = null): Page
    {
        $page = new Page();

        $page->saveFromInputs([
            'en' => [
                'title' => $title,
                'slug' => Str::slug($title),
                'data' => [
                    "structures"=> [],
                    "entities"=> [],
                    "media"=> []
                ],
                'status' => PublishableInterface::STATUS_PUBLISHED,
            ],
        ]);

        if ($authorId) {
            $page->saveAuthorId($authorId);
        }

        return $page;
    }
}
