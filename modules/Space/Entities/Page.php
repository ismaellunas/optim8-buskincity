<?php

namespace Modules\Space\Entities;

use App\Contracts\PublishableInterface;
use App\Models\Page as ModelPage;
use App\Models\PageTranslation as ModelPageTranslation;
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
        $baseSlug = Str::slug($title);

        $canContinueLoop = function ($slug) {
            return ModelPageTranslation::where('slug', $slug)
                ->where('locale', defaultLocale())
                ->exists();
        };

        $iteration = 0;

        do {
            $uniqueSlug = ($iteration > 0)
                ? $baseSlug . '-' . random_int(100000, 999999)
                : $baseSlug;

            $iteration ++;
        } while ($canContinueLoop($uniqueSlug));

        $page->saveFromInputs([
            defaultLocale() => [
                'title' => $title,
                'slug' => $uniqueSlug,
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
