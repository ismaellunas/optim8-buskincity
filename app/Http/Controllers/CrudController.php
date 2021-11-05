<?php

namespace App\Http\Controllers;

use App\Traits\FlashNotifiable;
use Illuminate\Support\Str;

abstract class CrudController extends Controller
{
    use FlashNotifiable;

    protected $model;
    protected $recordsPerPage = 15;
    protected $baseRouteName;
    protected $title;

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

    protected function getCreateTitle(): string
    {
        return 'Add New '.$this->title;
    }

    protected function getEditTitle(): string
    {
        return 'Edit '.$this->title;
    }

    protected function getIndexTitle(): string
    {
        return Str::plural($this->title);
    }
}
