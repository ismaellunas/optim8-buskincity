<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $modalContentClass;

    public function __construct(
        ?string $id = null,
        ?string $modalContentClass = null,
    ) {
        $this->id = $id;
        $this->modalContentClass = $modalContentClass;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal');
    }
}
