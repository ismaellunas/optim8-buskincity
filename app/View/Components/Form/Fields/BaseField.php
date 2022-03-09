<?php

namespace App\View\Components\Form\Fields;

use App\Services\TranslationService;
use Illuminate\View\Component;

abstract class BaseField extends Component
{
    public $label;
    public $value;

    protected $translate = false;
    protected $userLocale;
    protected $componentPrefix = "components.form.fields.";

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($label, $value, $translate, $userLocale)
    {
        $this->label = $label;
        $this->translate = $translate;
        $this->userLocale = $userLocale;
        $this->value = $value;
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

    protected function getViewName(): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/',
            '-$0',
            (new \ReflectionClass($this))->getShortName()
        ));
    }

    protected function setValueTranslation($value): ?string
    {
        if (!$this->translate || !$value) {
            return $value;
        }

        $currentLocale = TranslationService::currentLanguage();
        $userLocale = $this->userLocale ?? TranslationService::getDefaultLocale();

        return $value[$currentLocale] ?? $value[$userLocale];
    }
}
