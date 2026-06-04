<?php

namespace App\Rules;

use App\Services\UserScopeService;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Validates every city id in an array is within the actor's scope (T3.2).
 */
class InScopedCityIds implements InvokableRule
{
    public function __invoke($attribute, $value, $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $scope = app(UserScopeService::class);

        if ($scope->isGloballyScoped()) {
            return;
        }

        $allowed = $scope->scopedCityIds();

        foreach ($value as $cityId) {
            if (! in_array((int) $cityId, $allowed, true)) {
                $fail(__('You are not authorized to assign one or more of these cities.'));
                return;
            }
        }
    }
}
