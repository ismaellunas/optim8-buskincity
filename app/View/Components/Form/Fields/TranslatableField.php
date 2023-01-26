<?php

namespace App\View\Components\Form\Fields;

abstract class TranslatableField extends BaseField
{
    protected $isTranslated = false;
    protected $userLocale;

    public function __construct(
        array $field,
        ?string $userLocale = null
    ) {
        parent::__construct($field);

        $this->isTranslated = isset($field['is_translated']) ? $field['is_translated'] : false;
        $this->userLocale = $userLocale;

        $this->value = $this->setValueTranslation($this->value);
    }

    protected function setValueTranslation($value): ?string
    {
        if (!$this->isTranslated || !$value) {
            return $value;
        }

        $currentLocale = currentLocale();
        $userLocale = $this->userLocale ?? defaultLocale();

        return $value[$currentLocale] ?? $value[$userLocale];
    }
}
