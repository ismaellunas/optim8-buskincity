<?php

namespace Modules\Space\Http\Requests\Frontend;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\Rule;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceEventService;

class SpaceEventRequest extends BaseFormRequest
{
    private $space;

    public function getSpace(): ?Space
    {
        if (is_null($this->space)) {
            $spaceId = decrypt($this->route('encryptedSpaceId'));

            $this->space = Space::find($spaceId);
        }

        return $this->space;
    }

    public function rules()
    {
        $space = $this->getSpace();

        return [
            'space' => [
                'nullable',
                Rule::in(app(SpaceEventService::class)
                    ->getSpaceRecordOptions($space)
                    ->pluck('id')
                ),
            ],
            'dates' => [
                'nullable',
                'array'
            ],
            'dates.*' => [
                'nullable',
                'date_format:Y-m-d'
            ],
        ];
    }

    public function authorize()
    {
        try {
            $space = $this->getSpace();

            return !is_null($space);

        } catch (DecryptException $th) {

            return false;
        }
    }
}
