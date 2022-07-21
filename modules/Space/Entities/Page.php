<?php

namespace Modules\Space\Entities;

use App\Models\Page as ModelPage;
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

    public function newQuery(bool $excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)->type(self::TYPE);
    }

    public function space()
    {
        return $this->hasOne(Space::class);
    }
}
