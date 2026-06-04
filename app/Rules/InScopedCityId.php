<?php

namespace App\Rules;

use App\Services\UserScopeService;
use Illuminate\Contracts\Validation\InvokableRule;

/**
 * Validates a single city id is within the actor's scoped cities (T3.2).
 */
class InScopedCityId implements InvokableRule
{
    public function __invoke($attribute, $value, $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $scope = app(UserScopeService::class);

        if ($scope->isGloballyScoped()) {
            return;
        }

        if (! $scope->cityIdIsInScope((int) $value)) {
            $fail(__('You are not authorized to use this city.'));
        }
    }
}
