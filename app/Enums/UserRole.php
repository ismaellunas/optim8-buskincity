<?php

namespace App\Enums;

/**
 * Canonical registry of role names.
 *
 * Values MUST match the role names stored in the `roles` table exactly.
 * Two naming conventions currently coexist (Title Case for the original core
 * roles, snake_case for newer scoped roles); this enum preserves them as-is so
 * existing `model_has_roles` rows keep resolving. Do NOT rename values without a
 * data migration.
 *
 * Prefer referencing roles through this enum (or `config('permission.role_names.*')`)
 * instead of hardcoding strings across the codebase.
 */
enum UserRole: string
{
    case SUPER_ADMINISTRATOR = 'Super Administrator';
    case ADMINISTRATOR = 'Administrator';
    case AUTHOR = 'Author';
    case PERFORMER = 'Performer';
    case CITY_ADMINISTRATOR = 'city_administrator';
    case SPECIAL_EVENTS_ADMIN = 'special_events_admin';

    /**
     * All canonical role name strings.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }
}
