<?php

namespace Modules\Space\Entities;

use App\Models\Page as ModelPage;

class Page extends ModelPage
{
    protected const TYPE = 'space';

    protected $attributes = [
        'type' => self::TYPE,
    ];

    public function newQuery(bool $excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)->type(self::TYPE);
    }
}
