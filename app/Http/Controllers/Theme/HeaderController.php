<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HeaderController extends Controller
{
    private $baseRouteName = 'admin.theme.header';
    private $componentName = 'Theme/Header/';

    public function index()
    {
        return Inertia::render($this->componentName.'Index',[
            'baseRouteName' => $this->baseRouteName,
        ]);
    }
}
