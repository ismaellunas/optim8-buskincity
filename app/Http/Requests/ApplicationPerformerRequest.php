<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;

class ApplicationPerformerRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => [
                'required',
                'max:128',
            ],
            'last_name' => [
                'required',
                'max:128',
            ],
            'company' => [
                'max:128',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'phone.number' => [
                'required',
                'max:20',
                'phone:phone.country',
            ],
            'phone.country' => [
                'required_with:phone.number',
            ],
            'stage_name' => [
                'required',
                'max:64',
            ],
            'discipline' => [
                'required',
                'max:64',
            ],
            'address' => [
                'max:128',
            ],
            'city' => [
                'required',
                'max:64',
            ],
            'postal_code' => [
                'required',
                'max:32',
            ],
            'country' => [
                'required',
                'max:2',
            ],
            'about_you' => [
                'required',
                'max:2048',
            ],
            'performance_description' => [
                'required',
                'max:2048',
            ],
            'fees_per_day_corporate_gigs' => [
                'required',
                'max:16',
            ],
            'fees_per_day_private_gigs' => [
                'required',
                'max:16',
            ],
            'fees_per_day_festival_gigs' => [
                'required',
                'max:16',
            ],
            'facebook' => [
                'nullable',
                'url',
            ],
            'twitter' => [
                'nullable',
                'url',
            ],
            'instagram' => [
                'nullable',
                'url',
            ],
            'youtube' => [
                'nullable',
                'url',
            ],
            'other_links' => [
                'nullable',
                'array',
            ],
            'other_links.*' => [
                'url',
            ],
            'promotional_video' => [
                'required',
                'url',
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $otherLinks = array_filter(explode(',', str_replace(' ', '', $this->other_links)));

        $this->merge(['other_links' => $otherLinks]);
    }

    public function customAttributes(): array
    {
        $attributes = [];

        $keys = array_keys($this->rules());

        foreach ($keys as $key) {
            $attributes[$key] = Str::of($key)
                ->replace('_', ' ')
                ->replace('.*', '')
                ->title()
                ->__toString();
        }

        $attributes['phone.number'] = 'Phone';

        return $attributes;
    }
}
