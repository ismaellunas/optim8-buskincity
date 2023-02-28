<?php

namespace Modules\FormBuilder\Services;

class FormEntryService
{
    public function readOptions(): array
    {
        return [
            [ 'id' => null, 'value' => 'All' ],
            [ 'id' => 1, 'value' => 'Read' ],
            [ 'id' => 0, 'value' => 'Unread' ],
        ];
    }
}
