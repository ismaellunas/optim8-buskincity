<?php

namespace Modules\Space\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Space\Entities\Space;

/**
 * Enforces Country → City → Pitch/SE hierarchy and infers type_id (FR-CA-2).
 *
 * The type dropdown is removed from the UI; type is derived from the parent
 * (and the actor's role for scoped admins).
 */
class SpaceHierarchyService
{
    private const TYPE_COUNTRY = 'Country';

    private const TYPE_CITY = 'City';

    private const TYPE_PITCH = 'Pitch';

    private const TYPE_SPECIAL = 'Special Events / Festivals';

    /**
     * @param  array<string, mixed>  $inputs
     * @return array<string, mixed>
     */
    public function resolveInputs(array $inputs, User $user, ?Space $existing = null): array
    {
        $parentId = $inputs['parent_id'] ?? null;
        $parent = $parentId ? Space::with('type')->find($parentId) : null;

        $typeId = $existing
            ? (int) $existing->type_id
            : $this->inferTypeIdForCreate($parent, $user);

        $this->assertValidHierarchy($parent, $typeId, $user);

        $inputs['type_id'] = $typeId;

        return $inputs;
    }

    public function inferTypeIdForCreate(?Space $parent, User $user): int
    {
        if ($user->isSpecialEventsAdmin()) {
            return $this->requireTypeId(self::TYPE_SPECIAL);
        }

        if ($user->hasRole(config('permission.role_names.city_admin'))) {
            return $this->requireTypeId(self::TYPE_PITCH);
        }

        if (! $parent) {
            return $this->requireTypeId(self::TYPE_COUNTRY);
        }

        return match ($this->spaceTypeName($parent)) {
            self::TYPE_COUNTRY => $this->requireTypeId(self::TYPE_CITY),
            self::TYPE_CITY => $this->requireTypeId(self::TYPE_PITCH),
            default => throw ValidationException::withMessages([
                'parent_id' => [__('Invalid parent for a new space.')],
            ]),
        };
    }

    public function assertValidHierarchy(?Space $parent, int $typeId, User $user): void
    {
        $typeName = $this->typeName($typeId);

        if ($user->hasRole(config('permission.role_names.city_admin')) || $user->isSpecialEventsAdmin()) {
            if (! $parent || $this->spaceTypeName($parent) !== self::TYPE_CITY) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('Locations must be created under a city.')],
                ]);
            }
        }

        if ($typeName === self::TYPE_COUNTRY && $parent) {
            throw ValidationException::withMessages([
                'parent_id' => [__('Countries must be top-level spaces.')],
            ]);
        }

        if ($typeName === self::TYPE_CITY) {
            if ($parent && $this->spaceTypeName($parent) === self::TYPE_CITY) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('A city cannot be placed under another city.')],
                ]);
            }

            if ($parent && $this->spaceTypeName($parent) !== self::TYPE_COUNTRY) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('Cities must be created under a country.')],
                ]);
            }
        }

        if (in_array($typeName, [self::TYPE_PITCH, self::TYPE_SPECIAL], true)) {
            if (! $parent || $this->spaceTypeName($parent) !== self::TYPE_CITY) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('Pitches must be created under a city.')],
                ]);
            }
        }
    }

    private function typeName(?int $typeId): ?string
    {
        if (! $typeId) {
            return null;
        }

        return app(SpaceService::class)->types()->firstWhere('id', $typeId)?->name;
    }

    private function spaceTypeName(Space $space): ?string
    {
        return $space->type?->name ?? $this->typeName($space->type_id);
    }

    private function requireTypeId(string $name): int
    {
        $id = app(SpaceService::class)->types()->firstWhere('name', $name)?->id;

        if (! $id) {
            throw ValidationException::withMessages([
                'type_id' => [__('The :type space type is not configured.', ['type' => $name])],
            ]);
        }

        return (int) $id;
    }
}
