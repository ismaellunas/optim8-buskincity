<?php

namespace App\Services;

use App\Entities\Caches\SettingCache;
use App\Models\Setting;
use Illuminate\Support\Collection;

class StripeSettingService
{
    private $casts = [
        'stripe_amount_options' => 'object',
        'stripe_application_fee_percentage' => 'float',
        'stripe_default_country' => 'string',
        'stripe_is_enabled' => 'bool',
        'stripe_minimal_amounts' => 'array',
        'stripe_payment_currencies' => 'array',
        'stripe_color_primary' => 'string',
        'stripe_color_secondary' => 'string',
    ];

    public function getKeys(): array
    {
        return array_keys($this->casts);
    }

    public function save(string $key, mixed $value): Setting
    {
        $dataType = $this->casts[$key] ?? 'string';

        switch ($dataType) {
            case 'array': $convertedValue = json_encode($value); break;
            case 'bool': $convertedValue = (int) $value; break;
            case 'float': $convertedValue = (float)$value; break;
            case 'int': $convertedValue = (int)$value; break;
            case 'object': $convertedValue = json_encode($value); break;
            default: $convertedValue = $value;
        }

        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $convertedValue]
        );
    }

    public function getAll(): Collection
    {
        $settings = Setting::whereIn('key', $this->getKeys())
            ->select(['key', 'value'])
            ->get();

        return $settings->mapWithKeys(function ($setting) {

            $dataType = $this->casts[$setting->key] ?? 'string';

            $value = null;

            switch ($dataType) {
                case 'array': $value = json_decode($setting->value); break;
                case 'bool': $value = (bool)$setting->value; break;
                case 'float': $value = (float)$setting->value; break;
                case 'int': $value = (int)$setting->value; break;
                case 'object': $value = (object)json_decode($setting->value); break;
                default: $value = $setting->value;
            }

            return [$setting->key => $value];
        });
    }

    public function isEnabled(): bool
    {
        return app(SettingCache::class)->remember('stripe_is_enabled', function () {
            return (bool) Setting::key('stripe_is_enabled')->value('value');
        });
    }

    public function getDefaultCountry(): ?string
    {
        return Setting::key('stripe_default_country')->value('value');
    }

    public function getAmountOptions(): array
    {
        $currencyAmountOptions = Setting::key('stripe_amount_options')
            ->value('value');

        return (
            $currencyAmountOptions
            ? (array) json_decode($currencyAmountOptions)
            : []
        );
    }

    public function getAvailableCurrencyOptions(): array
    {
        $paymentCurrencies = Setting::key('stripe_payment_currencies')
            ->value('value');

        if ($paymentCurrencies) {
            $paymentCurrencies = (array) json_decode($paymentCurrencies);

            return array_map(
                function ($currency) {
                    return [
                        'id' => $currency,
                        'value' => $currency,
                    ];
                },
                $paymentCurrencies
            );
        }

        return [];
    }

    public function getCountrySpecs(): ?Setting
    {
        return Setting::firstWhere([
            'key' => 'stripe_country_specs',
        ]);
    }

    public function saveCountrySpecs(array $countrySpecs)
    {
        $setting = Setting::firstOrNew(['key' => 'stripe_country_specs']);

        $setting->value = json_encode($countrySpecs);
        $setting->updated_at = now();

        $setting->save();
    }

    public function displayNames(): Collection
    {
        return Setting::group('stripe')->pluck('display_name', 'key');
    }

    public function defaultPrimaryColor(): string
    {
        return '#395dbf';
    }

    public function defaultSecondaryColor(): string
    {
        return '#fcd42f';
    }

    public function primaryColor(): string
    {
        return Setting::key('stripe_color_primary')
            ->group('stripe')
            ->value('value');
    }

    public function secondaryColor(): string
    {
        return Setting::key('stripe_color_secondary')
            ->group('stripe')
            ->value('value');
    }
}
