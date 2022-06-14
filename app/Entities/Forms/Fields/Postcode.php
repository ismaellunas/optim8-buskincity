<?php

namespace App\Entities\Forms\Fields;

use Illuminate\Support\Str;

class Postcode extends BaseField
{
    protected $type = "Text";

    public $leftIcon;
    public $maxlength;
    public $placeholder;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->leftIcon = $data['left_icon'] ?? null;
        $this->maxlength = $data['maxlength'] ?? null;
        $this->placeholder = $data['placeholder'] ?? null;
    }

    public function schema(): array
    {
        $schema = [
            'left_icon' => $this->leftIcon,
            'maxlength' => $this->maxlength ?? $this->max() ?? null,
            'placeholder' => $this->placeholder,
        ];

        return array_merge(parent::schema(), $schema);
    }

    protected function max(): ?int
    {
        $rules = collect($this->validationRules()[$this->name]);

        $maxRule = $rules->first(function ($rule) {
            return Str::startsWith($rule, 'max:');
        });

        if ($maxRule) {
            return (int) Str::replaceFirst('max:', '', $maxRule);
        }

        return null;
    }

    public function validationRules(): array
    {
        $rules = parent::validationRules();

        $country = request()->country ?? null;

        if ($country) {
            $rules[$this->name][] = 'postal_code:'.$country;
        }

        return $rules;
    }
}
