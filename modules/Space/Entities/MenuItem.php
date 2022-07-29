<?php

namespace Modules\Space\Entities;

use App\Models\MenuItem as AppMenuItem;
use Modules\Space\Entities\Page;

class MenuItem extends AppMenuItem
{
    /**
     * @override
    */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
