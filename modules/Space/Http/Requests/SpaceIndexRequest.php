<?php

namespace Modules\Space\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use Modules\Space\Services\SpaceService;

class SpaceIndexRequest extends BaseFormRequest
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
        $typeIds = app(SpaceService::class)->typeOptions()->pluck('id')->toArray();

        return [
            'term' => [
                'nullable',
                'max:255',
            ],
            'types' => [
                'sometimes',
                'array',
            ],
            'types.*' => [
                Rule::in($typeIds),
            ],
        ];
    }
}
