<?php

namespace App\Http\Requests;

use App\Models\Post;
use App\Rules\AlphaNumericDash;
use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'scheduled_on' => ['date'],
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

    public function attributes(): array
    {
        return [];
    }
}
