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

3. **City Data Library** (Recommended)
   - Install `country-state-city` npm package for fetching cities by country:
   ```bash
   npm install country-state-city
   ```
   - **Why this library?**
     - 250+ countries, 5,000+ states, 150,000+ cities
     - Lightweight and actively maintained
     - Simple API: `City.getCitiesOfCountry(countryCode)`
     - Supports filtering cities by state: `City.getCitiesOfState(countryCode, stateCode)`
   - **Usage Example (Frontend)**:
   ```javascript
   import { Country, City } from 'country-state-city';
   
   // Get all cities for a country
   const cities = City.getCitiesOfCountry('PH'); // Philippines
   
   // Example output format:
   // [{ name: 'Manila', countryCode: 'PH', stateCode: '00', latitude: '14.60', longitude: '120.98' }, ...]
   ```
   - **Benefits for City Administrator Role**:
     - Provides normalized, consistent city names
     - Eliminates typos and variations ("Manila" vs "manila")
     - Enables city dropdown/autocomplete in admin UI
     - Works for both backend validation and frontend UX
   - **Alternative**: If you prefer server-side data, create API endpoint using the same library on backend
4. **User Model**
   - Add relationship:
   ```php
   public function adminCities(): BelongsToMany {
       return $this->belongsToMany(City::class, 'city_user');
   }
   public function isCityAdmin(int $cityId): bool {
       return $this->hasRole('city_administrator') && $this->adminCities()->where('id', $cityId)->exists();
   }
   ```
5. **Policy / Gate**
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
6. **Admin UI**
   - Add "City Administrator" to role dropdown.
   - Add multi‑select of cities; persist via API.
7. **API Endpoints**
   - `GET /api/admin/users/{id}/cities`
   - `POST /api/admin/users/{id}/cities` (sync city IDs).
8. **Tests**
   - Unit tests for `isCityAdmin` and policy.
   - Feature tests for the new API routes.
9. **Documentation**
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
