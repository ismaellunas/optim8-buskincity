# City Administrator Role – Implementation Guide

This document outlines the steps to add a **"City Administrator"** role that can manage events for specific cities.

## Overview
- Uses **Spatie Laravel‑Permission** for roles/permissions.
- Introduces a many‑to‑many relation `city_user` to link users with cities they administer.
- Provides a permission `manage city events` scoped by city.

## Checklist
1. **Create Role & Permission**
   - Add a seeder (`CityAdminRoleSeeder` / `CityAdminPermissionSeeder`) that creates:
     - Role: `city_administrator`
     - Permission: `manage city events`
   - Assign the permission to the role.
2. **Pivot Table**
   - Migration `create_city_user_table` with columns `user_id`, `city_id`.
3. **User Model**
   - Add relationship:
   ```php
   public function adminCities(): BelongsToMany {
       return $this->belongsToMany(City::class, 'city_user');
   }
   public function isCityAdmin(int $cityId): bool {
       return $this->hasRole('city_administrator') && $this->adminCities()->where('id', $cityId)->exists();
   }
   ```
4. **Policy / Gate**
   - In `EventPolicy` (or new `CityEventPolicy`):
   ```php
   public function manage(User $user, Event $event) {
       return $user->isCityAdmin($event->city_id);
   }
   ```
   - Register gate in `AuthServiceProvider`:
   ```php
   Gate::define('manage-city-event', [EventPolicy::class, 'manage']);
   ```
5. **Admin UI**
   - Add "City Administrator" to role dropdown.
   - Add multi‑select of cities; persist via API.
6. **API Endpoints**
   - `GET /api/admin/users/{id}/cities`
   - `POST /api/admin/users/{id}/cities` (sync city IDs).
7. **Tests**
   - Unit tests for `isCityAdmin` and policy.
   - Feature tests for the new API routes.
8. **Documentation**
   - Update `CODEBASE_DOCUMENTATION.md` under *Roles & Permissions*.

## Quick Commands
```bash
php artisan make:migration create_city_user_table
php artisan make:seeder CityAdminRoleSeeder
php artisan make:seeder CityAdminPermissionSeeder
php artisan migrate
php artisan db:seed --class=CityAdminRoleSeeder
php artisan db:seed --class=CityAdminPermissionSeeder
```

Follow the checklist in order; each step builds on the previous one, ensuring the role is fully functional and secure.
