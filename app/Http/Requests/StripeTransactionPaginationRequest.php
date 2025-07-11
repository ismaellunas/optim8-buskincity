<?php

namespace App\Http\Requests;

class StripeTransactionPaginationRequest extends BaseFormRequest
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
        $rules = [
            'nullable',
            'string',
        ];

        return [
            'startingAfter' => $rules,
            'endingBefore' => $rules,
        ];
    }
}
