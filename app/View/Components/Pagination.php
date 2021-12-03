<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Pagination extends Component
{
    public $paginator;
    public $elements;

    private $queryParams;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($paginator, $queryParams = null)
    {
        $this->paginator = $paginator;
        $this->queryParams = $queryParams;

        $this->elements = $paginator->links()['elements'] ?? [];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.pagination');
    }

    public function serializedParams(): string
    {
        if (!empty($this->queryParams)) {
            return '&' . http_build_query($this->queryParams, '', '&');
        }
        return '';
    }
}
