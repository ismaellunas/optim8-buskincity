<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

class CrudController extends Controller
{
    protected $model;
    protected $recordsPerPage = 15;
    protected $baseRouteName;

    protected function generateFlashMessage($message): void
    {
        session()->flash('message', $message);
    }
}
