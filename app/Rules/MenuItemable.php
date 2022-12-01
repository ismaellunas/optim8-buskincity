<?php

namespace App\Rules;

use App\Services\MenuService;
use Illuminate\Contracts\Validation\Rule;

class MenuItemable implements Rule
{
    private $type;

    public function __construct($type)
    {
        $this->type = $type;
    }
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $type = collect(app(MenuService::class)->getMenuItemTypeOptions())
            ->where('id', $this->type)
            ->first();

        $model = $type['model'] ?? null;

        if (class_exists($model)) {
            return !is_null($model::find($value));
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.menu_itemable');
    }
}
