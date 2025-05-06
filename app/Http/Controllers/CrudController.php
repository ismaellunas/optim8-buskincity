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
                'title' => $this->title(),
            ],
            $additionalData
        );
    }

    protected function getCreateTitle(): string
    {
        return Str::title(
            __('Add new :resource', ['resource' => $this->title()])
        );
    }

    protected function getEditTitle(): string
    {
        return Str::title(
            __('Edit :resource', ['resource' => $this->title()])
        );
    }

    protected function getIndexTitle(): string
    {
        return Str::plural($this->title());
    }

    protected function title(): string
    {
        return __($this->title);
    }
}
