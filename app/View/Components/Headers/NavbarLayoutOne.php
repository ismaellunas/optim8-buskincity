<?php

namespace App\View\Components\Headers;

use App\View\Components\Layouts\Master;

class NavbarLayoutOne extends Master
{

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.headers.navbar-layout-one');
    }
}
