<?php

namespace App\Http\Controllers;

use App\Jobs\CompileThemeCss;
use App\Traits\FlashNotifiable;

abstract class ThemeOptionController
{
    use FlashNotifiable;

    protected function getData(array $additionalData = []): array
    {
        return array_merge(
            [
                'baseRouteName' => $this->baseRouteName,
                'title' => $this->title,
            ],
            $additionalData
        );
    }

    protected function generateNewStyleProcess()
    {
        CompileThemeCss::dispatch();
    }
}
