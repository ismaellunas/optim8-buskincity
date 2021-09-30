<?php

namespace App\Http\Controllers;

abstract class CrudController extends Controller
{
    protected $model;
    protected $recordsPerPage = 15;
    protected $baseRouteName;

    protected function generateFlashMessage($message): void
    {
        session()->flash('message', $message);
    }

    protected function getData(array $additionalData = []): array
    {
        return array_merge(
            [
                'baseRouteName' => $this->baseRouteName,
            ],
            $additionalData
        );
    }
}
