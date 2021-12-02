<?php

namespace App\View\Components\Builder;

use Illuminate\View\Component;

class Columns extends Component
{
    public $uid;
    public $columns;
    public $entities;
    public $locale;
    public $images;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($uid, $columns, $entities, $locale, $images = [])
    {
        $this->uid = $uid;
        $this->columns = $columns;
        $this->entities = $entities;
        $this->locale = $locale;
        $this->images = $images;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.builder.columns');
    }
}
