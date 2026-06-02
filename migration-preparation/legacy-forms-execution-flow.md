# Legacy / Core Forms — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — trace of the core/legacy form system used for internal entity management (Users, Settings) which exists separately from the dynamic Form Builder module.

---

## 1. Internal Form Lifecycle (Schema & Persistence)

Unlike the dynamic Form Builder, the Core Form system is "Route-Aware" and uses bridge classes to map form inputs to database models.

### Flow: Loading a Form Schema (`GET /forms/schemas`)
1. **Controller**: `App\Http\Controllers\FormController@getSchemas`
2. **Parameters**: `route_name` (e.g., `admin.profile.show`) and `id` (Entity ID).
3. **Logic Decision (`getFormLocation`)**:
    - The `FormService` maintains a hardcoded map of routes to "Location" classes:
        - `admin.profile.show` → `UserProfileLocation`
        - `admin.users.edit` → `UserEditLocation`
4. **Data Acquisition**:
    - The resolved "Location" class (e.g., `UserProfileLocation`) executes `getValues()` to pull existing data directly from the `users` table based on the provided Entity ID.
5. **Combined Context**: The service merges the field definitions (from the `forms` table) with the current values (from the entity model) into a unified JSON schema for the frontend.

---

## 2. Dynamic Update Layer (`POST /forms/save`)

1. **Controller**: `App\Http\Controllers\FormController@submit`
2. **Bridge Execution**: 
    - The `FormService` instantiates the corresponding `FormLocation` class.
    - It calls `$formLocation->save($fields, $inputs)`.
3. **Persistence**:
    - Unlike the Form Builder (which uses EAV/Meta tables), the Legacy Form system typically updates **direct columns** on the targeted model (e.g., updating `first_name` directly on the `User` model).
4. **Side Effects**:
    - **Validation**: Rules are resolved via `$form->rules($location)`, allowing for context-specific validation (e.g., "required only on create").

---

## 3. Hidden Dependencies & Entities

| Component | Dependency | Role |
|-----------|------------|------|
| **Form Entity** | `App\Entities\Form` | Acts as the "Controller" for field structure and validation rules. |
| **Location** | `App\Entities\Forms\Locations` | The "Adapter" that knows how to read/write to specific Eloquent models. |
| **Traits** | `FlashNotifiable` | Standardized mechanism for returning "Saved" flash messages in Inertia. |

---

## 4. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `forms` | Stores form grouping data and associated `location_route`. |
| `field_groups` | Defines the layout and groupings of fields within a legacy form. |
| `users` (or others) | The direct target of the Legacy Form system's updates. |

---

## 5. Migration Critical Notes

1. **Hardcoded Route Mapping**: The mapping between `admin.*` routes and their Location classes is hardcoded in `FormService.php`. If route names change during migration, the forms will fail to load (403 or 404).
2. **Direct Attributes**: This system bypasses the `user_metas` table for core fields. Migration scripts must prioritize standardizing the `users` table schema to match the expectations of the `UserEditLocation` class.
3. **Validation Logic**: Some validation rules are defined inside the `Entities\Forms\Form` classes rather than standard Request classes. These classes must be audited for environment-specific constraints.
4. **User-Space Overlap**: Historically, this system was used for "City Administrators" to manage users within their regional silos. Ensure `entityId` checks in the Location classes are preserved to prevent cross-tenant data access.

---

*End of Legacy / Core Forms Execution Flow*
