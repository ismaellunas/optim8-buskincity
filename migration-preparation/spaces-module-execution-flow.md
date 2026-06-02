# Spaces (Module) — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — trace of the Space module's venue management, hierarchical content resolution, and deep integration with Ecommerce/Booking products.

---

## 1. Space Creation & Product Sync (`SpaceController`)

Spaces act as the "Physical Parent" of activities. A single space can host multiple bookable products.

### Flow: Creating a Space (`POST /admin/spaces`)
1. **Controller**: `Modules\Space\Http\Controllers\SpaceController@store`
2. **Logic Decision (`createProductForSpace`)**:
    - **Trigger**: If `create_product` is enabled in the UI.
    - **Action**: Creates a corresponding `Modules\Ecommerce\Entities\Product`.
    - **Linkage**: Sets `productable_type` to `Space` and `productable_id` to the new space ID.
3. **Data Inheritance**:
    - The Space's address, city, and GPS coordinates are automatically injected into the Product's `meta['locations']`.
    - This ensures that a booking made via the product automatically knows its venue context.
4. **Schedule Initialization**: Automatically creates a `Modules\Booking\Entities\Schedule` record for the product, enabling it for the calendar system.

---

## 2. Hierarchical Routing & Localization (`Frontend\SpaceController`)

Spaces support nested slugs and multi-language landing pages.

### Flow: Resolving a Space Page (`GET /spaces/{slugs}`)
1. **Route**: Dynamic slug resolution via `slugs` parameter (regex: `.+`).
2. **Service**: `PageSpaceService@getPageTranslationFromRequest()`.
    - Matches the slug against the `space_page_translations` table for the current locale.
3. **Template Discovery Engine**:
    - The system attempts to find a Blade view using a strict priority list:
        - `page-space_id_{id}-{lang}.blade.php` (Most specific)
        - `page-space_slug_{slug}.blade.php`
        - `page-space_type_{type}.blade.php` (Type-based fallback, e.g., "Library")
        - `page-space.blade.php` (Generic fallback)
4. **Logic**: If the requested locale isn't published, it attempts a fallback to the `defaultLocale` before redirecting to the homepage.

---

## 3. Managerial Access Control

The Space module implements a granular permission model designed for "City Administrators."

- **Role Logic**: `City Administrators` are automatically synced to the spaces they create via `user->spaces()->syncWithoutDetaching()`.
- **Filtering**: `SpaceController@index` restricts the listing based on `city_id` for city admins, while super-admins see the full global tree.

---

## 4. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Asset Sync** | `MediaService` | Logo and Cover images are managed as polymorphic relations via the central Media library. |
| **SEO** | `PageTranslation` | Frontend metadata (Meta Title/Description) is pulled from the linked Page entity, not the Space entity itself. |
| **Booking** | `Modules/Booking` | The "Create Product" feature will silently fail or be hidden if the Booking module is disabled. |
| **Google Maps** | `SettingService` | Uses the global Google API key for geolocation and "Direction" URL generation in the frontend. |

---

## 5. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `space_spaces` | The primary record (Address, Lat/Long, Type, Status). |
| `space_space_types` | Taxonomy for categorizing venues (e.g., Indoor, Outdoor, Stage). |
| `space_pages` / `space_page_translations` | The content layer for the space's frontend landing page. |
| `space_space_managers` | Pivot table linking users to specific venues they can edit. |
| `ecommerce_products` | Linked via `productable_id` when bookable events are hosted at a space. |

---

## 6. Migration Critical Notes

1. **Hierarchy Integrity**: Spaces use a nested set or parent-child structure. Ensure `parent_id` relationships are maintained during migration, or the `getTopParents()` listing will break.
2. **Slug Conflicts**: Since Spaces utilize dynamic slug resolution (`{slugs}`), ensure that Space slugs do not collide with core CMS page slugs.
3. **Location Meta**: Product locations are **snapshots** of Space locations at the time of product creation. If a Space address is updated, a migration script might be needed to sync that change to all linked `ecommerce_products.meta`.
4. **City-Admin Mapping**: The `city_id` on the `spaces` table must match the IDs in the `cities` lookup table for the City Administrator role logic to function.

---

*End of Spaces Module Execution Flow*
