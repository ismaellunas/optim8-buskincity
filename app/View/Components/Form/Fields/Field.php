<?php

namespace App\View\Components\Form\Fields;

use Illuminate\View\Component;

class Field extends Component
{
    public $title;
    public $fields;
    public $userLocale;

    private $componentPrefix = "form.fields.";

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $title, array $fields, $userLocale)
    {
        $this->title = $title;
        $this->fields = $fields;
        $this->userLocale = $userLocale;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.form.fields.field');
    }

    public function componentName($type): string
    {
        return $this->componentPrefix . strtolower(
            preg_replace('/(?<!^)[A-Z]/', '_$0', $type)
        );
    }
}
