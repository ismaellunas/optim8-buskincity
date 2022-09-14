<?php

namespace App\Entities\Forms\Fields;

use Illuminate\Support\Str;

class Text extends TranslatableField
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

        if (isset($this->validation['rules']['regex'])) {
            $this->adjustRegexRule();
        }
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

    private function adjustRegexRule(): void
    {
        $regex = $this->validation['rules']['regex'];

        try {
            preg_match($regex, '');
        } catch (\Throwable $th) {
            $regex = null;
        }

        if (!$regex) {
            unset($this->validation['rules']['regex']);
        }
    }
}
