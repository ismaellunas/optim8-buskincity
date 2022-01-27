<?php

namespace App\Entities\Forms\Fields;

use App\Services\CountryService;
use Propaganistas\LaravelPhone\PhoneNumber;

class Phone extends BaseField
{
    protected $type = "Phone";

    public $defaultCountry;

    public $defaultValue = [
        'country' => null,
        'number' => null,
    ];

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->maxlength = $data['maxlength'] ?? null;
        $this->placeholder = $data['placeholder'] ?? null;
        $this->defaultCountry = $this->getDefaultCountryIsoAlpha2();
    }

    public function schema(): array
    {
        $schema = [
            'defaultCountry' => $this->defaultCountry,
            'countryOptions' => app(CountryService::class)
                ->getPhoneCountryOptions()
                ->all(),
        ];

        return array_merge(parent::schema(), $schema);
    }

    protected function adjustNullableRule(&$rules)
    {
        if (!$this->isRequired()) {
            $rules[$this->name.".number"][] = 'nullable';
        }
    }

    public function validationRules(): array
    {
        $rules[$this->name.".number"] = $this->validation['rules'] ?? [];

        $this->adjustNullableRule($rules);

        $rules[$this->name.".number"][] = 'phone:'.$this->name.'.country';
        $rules[$this->name.".number"][] = 'nullable';

        $rules[$this->name.".country"][] = 'required_with:'.$this->name.'.number';

        return $rules;
    }

    public function getDataToBeSaved(array $inputs): array
    {
        $data = parent::getDataToBeSaved($inputs);

        $data[$this->name.'_e164'] = null;

        if (
            !empty($data)
            && !empty($data[$this->name]['number'])
        ) {
            $data[$this->name.'_e164'] = PhoneNumber::make(
                $data[$this->name]['number'],
                $data[$this->name]['country']
            )->formatE164();
        }

        return $data;
    }

    public function getDefaultCountryIsoAlpha2(): ?string
    {
        return "US";
    }

    public function getLabels(array $inputs = []): array
    {
        return [
            $this->name.'.number' =>  $this->label,
            $this->name.'.country' =>  $this->label,
        ];
    }
}
