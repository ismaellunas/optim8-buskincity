<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Column extends Component
{
    public $uid;
    public $components;
    public $componentPrefix = 'builder.content.';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($uid, $components)
    {
        $this->uid = $uid;
        $this->components = $components;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.builder.column');
    }

    public function componentName($componentName): string
    {
        return $this->componentPrefix . strtolower(
            preg_replace('/(?<!^)[A-Z]/', '_$0', $componentName)
        );
    }
}
