<?php

namespace App\View\Components\Form\Fields;

use Illuminate\View\Component;

abstract class BaseField extends Component
{
    public $label;
    public $value;

    protected $componentPrefix = "components.form.fields.";

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        array $field,
    ) {
        $this->label = isset($field['label']) ? $field['label'] : null;
        $this->value = isset($field['value']) ? $field['value'] : null;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view($this->componentPrefix . $this->getViewName());
    }

    public function getViewName(): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/',
            '-$0',
            (new \ReflectionClass($this))->getShortName()
        ));
    }
}
