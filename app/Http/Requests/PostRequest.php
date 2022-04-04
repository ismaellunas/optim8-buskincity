<?php

namespace App\Http\Requests;

use App\Models\Post;
use App\Rules\AlphaNumericDash;

class PostRequest extends BaseFormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => [
                'required',
                'max:250',
                new AlphaNumericDash()
            ],
            'title' => [
                'required',
                'max:250'
            ],
            'excerpt' => ['max:500'],
            'locale' => [
                'required',
                'max:3'
            ],
            'meta_description' => ['max:250'],
            'meta_title' => ['max:250'],
            'scheduled_at' => [
                'nullable',
                'date',
                'required_if:status,'.Post::STATUS_SCHEDULED
            ],
            'status' => [
                'integer',
                'in:'.collect(Post::getStatusOptions())->implode('id', ','),
            ],
            'cover_image_id' => [
                'nullable',
                'integer'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.required_if' => (
                'The :attribute field is required when :other is '.
                collect(Post::getStatusOptions())
                    ->firstWhere('id', Post::STATUS_SCHEDULED)['value']
            ),
        ];
    }
}
