<?php

namespace App\Entities\Forms\Fields;

class Checkbox extends BaseField
{
    protected $type = 'Checkbox';

    public $trueValue = true;
    public $falseValue = false;
    public $text;
    public $isRaw;
    public $isBoolean;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        if (array_key_exists('true_value', $data)) {
            $this->trueValue = $data['true_value'];
        }

        if (array_key_exists('false_value', $data)) {
            $this->falseValue = $data['false_value'];
        }

        $this->text = $data['text'] ?? '';
        $this->isRaw = $data['is_raw'] ?? false;
        $this->isBoolean = $data['is_boolean'] ?? false;
    }

    public function schema(): array
    {
        $schema = [
            'true_value' => $this->trueValue,
            'false_value' => $this->falseValue,
            'text' => $this->text,
            'is_raw' => $this->isRaw,
        ];

        return array_merge(
            parent::schema(),
            $schema
        );
    }

    public function getDataToBeSaved(array $inputs): array
    {
        $data = [];

        if (array_key_exists($this->name, $inputs)) {
            $value = $inputs[$this->name];

            if ($this->isBoolean) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            $data[$this->name] = $value;
        }

        return $data;
    }
}
