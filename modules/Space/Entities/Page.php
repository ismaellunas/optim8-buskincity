<?php

namespace Modules\Space\Entities;

use App\Models\Page as ModelPage;
use Illuminate\Database\Eloquent\Builder;
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
}
