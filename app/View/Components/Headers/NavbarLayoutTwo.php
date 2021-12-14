<?php

namespace App\View\Components\Headers;

class NavbarLayoutTwo extends Header
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.headers.navbar-layout-two');
    }
}
