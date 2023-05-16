<?php

namespace App\Imports;

use App\Services\CountryService;
use App\Services\GlobalOptionService;
use App\Services\LanguageService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Helpers\HtmlToText;

class UserPerformerImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public Collection $data;

    public function collection(Collection $rows)
    {
        $this->data = collect();

        foreach ($rows as $row) {
            $phone = $row['mobile'] && $row['country']
                ? $this->phoneFormatter($row['mobile'],$row['country'])
                : [];
            $phoneE164 = ! empty($phone)
                ? $this->phoneE164Formatter($phone)
                : null;
            $discipline = $row['discipline']
                ? $this->getDisciplineValue($row['discipline'])
                : null;

            $this->data->push([
                'first_name' => $row['firstname'],
                'last_name' => $row['lastname'],
                'email' => $row['email'],
                'language_id' => $this->getLanguageId(),
                'metas' => [
                    'discipline' => $discipline,
                    'stage_name' => $row['stagecompanyname'],
                    'short_bio' => $this->translationValue(
                        HtmlToText::convert($row['biography'])
                    ),
                    'long_bio' => $this->translationValue(
                        HtmlToText::convert($row['performancedescription'])
                    ),
                    'phone' => ! empty($phoneE164)
                        ? $phone
                        : null,
                    'phone_e164' => $phoneE164,
                    'address' => $row['address1'],
                    'city' => $row['city'],
                    'postcode' => $row['zipcode'],
                    'country' => $row['country']
                        ? $this->getCountryCode($row['country'])
                        : 'US',
                    'facebook' => $row['facebook']
                        ? $this->addHttpsOnLink($row['facebook'])
                        : null,
                    'twitter' => $row['twitter']
                        ? $this->addHttpsOnLink($row['twitter'])
                        : null,
                    'instagram' => $row['instagram']
                        ? $this->addHttpsOnLink($row['instagram'])
                        : null,
                    'youtube' => null,
                    'tiktok' => null,
                    'promotional_video' => null,
                ],
            ]);
        }
    }

    private function getLanguageId(): int
    {
        return app(LanguageService::class)->getDefaultId();
    }

    private function translationValue(string $value = null): array
    {
        $translation = [];
        $supportedLanguageCode = app(LanguageService::class)
            ->getSupportedLanguages()
            ->pluck('code')
            ->all();

        foreach ($supportedLanguageCode as $key => $code) {
            if ($key == 0) {
                $translation[$code] = $value;
            } else {
                $translation[$code] = null;
            }
        }

        return $translation;
    }

    private function phoneFormatter(string $number, string $country): array
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        $country = app(CountryService::class)
            ->getPhoneCountryOptions()
            ->firstWhere('value', $country);

        if (! empty($country)) {
            if (Str::startsWith($number, $country['dial'])) {
                $number = Str::replaceFirst(
                    $country['dial'],
                    '',
                    $number
                );
            }

            return [
                'number' => $number,
                'country' => $country['id'],
            ];
        }

        return [];
    }

    private function phoneE164Formatter(array $phone): ?string
    {
        try {

            return (new PhoneNumber(
                    $phone['number'],
                    $phone['country']
                ))
                ->formatInternational();

        } catch (\Throwable $th) {

            return null;

        }
    }

    private function getCountryCode(string $country): ?string
    {
        return app(CountryService::class)
            ->getCountryOptions()
            ->where('value', $country)
            ->first()['id']
            ?? null;
    }

    private function getDisciplineValue(string $value): ?string
    {
        $result = app(GlobalOptionService::class)
            ->getDisciplineOptions()
            ->filter(function ($option) use ($value) {
                return false !== stripos($value, $option['id']);
            });

        if ($result) {
            return $result->first()['id']
                ?? null;
        }

        return null;
    }

    private function addHttpsOnLink(string $link): string
    {
        $prefix = 'https://';

        if (! Str::startsWith($link, $prefix)) {
            return $prefix . $link;
        }

        return $link;
    }
}
