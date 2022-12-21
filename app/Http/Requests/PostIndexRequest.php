<?php

namespace App\Http\Requests;

use App\Models\Post;
use App\Services\CategoryService;
use App\Services\LanguageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $categoryIds = app(CategoryService::class)->getCategoryOptions()
            ->pluck('value')
            ->toArray();

        $languageCodes = app(LanguageService::class)->getSupportedLanguageOptions()
            ->pluck('code')
            ->toArray();

        $status = collect(Post::getStatusOptions())
            ->pluck('value')
            ->map(function ($value) {
                return Str::lower($value);
            })
            ->toArray();

        return [
            'term' => [
                'nullable',
                'max:1024',
            ],
            'view' => [
                'nullable',
            ],
            'status' => [
                'nullable',
                Rule::in($status),
            ],
            'languages' => [
                'sometimes',
                'array',
            ],
            'languages.*' => [
                'string',
                Rule::in($languageCodes),
            ],
            'categories' => [
                'sometimes',
                'array',
            ],
            'categories.*' => [
                'numeric',
                Rule::in($categoryIds),
            ],
        ];
    }
}
