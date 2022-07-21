<?php

namespace Modules\Space\Entities;

use App\Models\Page as ModelPage;

class Page extends ModelPage
{
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
}
