# Database Investigation: Spaces, Space Users, and Space Events

## Executive Summary

This investigation documents the database tables `spaces`, `space_user`, and `space_events`, analyzing their structure, relationships, and data storage patterns, particularly focusing on **city data storage** for the City Administrator role implementation.

### Key Findings

- **City data**: Currently stored as text fields (`city`, `country_code`) in both `spaces` and `space_events` tables
- **No dedicated city table**: Cities are stored as free-text strings, not normalized in a separate table
- **User-Space relationship**: The `space_user` pivot table links users to spaces for management permissions
- **Recommendation**: A new normalized city table will be needed for the City Administrator role to avoid data inconsistency

---

## Table 1: `spaces`

### Purpose
Stores physical spaces/venues where events and activities take place. Supports hierarchical nested structure using the Nested Set pattern.

### Schema Definition

**Migration File**: [2022_07_07_115330_create_spaces_table.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Database/Migrations/2022_07_07_115330_create_spaces_table.php)

**Model**: [Space.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Entities/Space.php)

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `name` | varchar(128) | No | Space name |
| `address` | varchar(512) | Yes | Street address |
| **`city`** | **varchar** | **Yes** | **City name (free text)** |
| **`country_code`** | **varchar(3)** | **Yes** | **Country code (ISO 3166-1 alpha-3)** |
| `latitude` | double | Yes | GPS latitude |
| `longitude` | double | Yes | GPS longitude |
| `contacts` | json | Yes | Contact information array |
| `is_page_enabled` | boolean | No | Whether page is publicly accessible (default: false) |
| `type_id` | foreignId | Yes | Foreign key to `global_options` (space type) |
| `page_id` | foreignId | Yes | Foreign key to `pages` |
| `parent_id` | bigint | Yes | For nested set hierarchy |
| `_lft`, `_rgt` | int | Yes | Nested set columns (left/right boundaries) |
| `created_at`, `updated_at` | timestamp | No | Timestamps |

### Data Characteristics

> [!IMPORTANT]
> **City Storage**: The `city` field stores city names as **free-text strings**. There is no referential integrity or normalization. Examples might include "Manila", "Quezon City", "Makati", etc.

**Fillable Fields** (from model):
```php
['address', 'city', 'contacts', 'country_code', 'is_page_enabled', 
 'latitude', 'longitude', 'name', 'parent_id', 'type_id']
```

**Relationships**:
- `managers()` → Many-to-Many with `users` via `space_user` pivot
- `page()` → Belongs to `Page`
- `type()` → Belongs to `GlobalOption`
- `events()` → Has many `SpaceEvent`
- `parent()` / `children()` / `ancestors()` / `descendants()` → Nested set relationships

---

## Table 2: `space_user`

### Purpose
Pivot table that establishes a **many-to-many relationship** between users and spaces. Users linked via this table become "managers" of those spaces, granting them permission to manage the space and its events.

### Schema Definition

**Migration File**: [2022_07_14_090404_create_space_user_table.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Database/Migrations/2022_07_14_090404_create_space_user_table.php)

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `space_id` | foreignId | No | Foreign key to `spaces.id` (cascade delete/update) |
| `user_id` | foreignId | No | Foreign key to `users.id` (cascade delete/update) |

### Usage in Code

**Assignment**: Users are assigned as space managers via:
```php
$space->managers()->sync($request->managers);
```

**Location**: [SpaceController.php:L356](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Http/Controllers/SpaceController.php#L352-L363)

**Policy/Authorization**: User authorization for space management is checked using Laravel policies (e.g., `$user->can('manage', $space)`)

---

## Table 3: `space_events`

### Purpose
Stores events that occur within spaces. Each event is associated with a parent space and can optionally have its own address (if different from the parent space).

### Schema Definition

**Migration File**: [2022_07_28_120425_create_space_events_table.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Database/Migrations/2022_07_28_120425_create_space_events_table.php)

**Model**: [SpaceEvent.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Entities/SpaceEvent.php)

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint | No | Primary key |
| `title` | varchar(255) | No | Event title |
| `address` | text | Yes | Event address (if different from space) |
| **`city`** | **varchar** | **Yes** | **Event city (free text)** |
| **`country_code`** | **varchar(3)** | **Yes** | **Country code** |
| `latitude` | double | Yes | GPS latitude |
| `longitude` | double | Yes | GPS longitude |
| `started_at` | datetime | No | Event start date/time |
| `ended_at` | datetime | No | Event end date/time |
| `timezone` | varchar(32) | Yes | Timezone string |
| `is_same_address_as_parent` | boolean | No | Whether to use parent space's address (default: true) |
| `status` | varchar(23) | No | Publishing status (default: DRAFT) |
| `space_id` | foreignId | Yes | Foreign key to `spaces.id` (cascade delete/update) |
| `author_id` | foreignId | Yes | Foreign key to `users.id` (set null on delete) |
| `created_at`, `updated_at` | timestamp | No | Timestamps |

### Data Characteristics

> [!IMPORTANT]
> **City Storage**: Like `spaces`, the `city` field stores city names as **free-text strings** without normalization.

**Fillable Fields**:
```php
['address', 'started_at', 'ended_at', 'status']
```

**Relationships**:
- `space()` → Belongs to `Space`
- `author()` → Belongs to `User`

**Address Logic**:
- If `is_same_address_as_parent` = true, event inherits space's address
- Otherwise, uses own `address`, `city`, `country_code`, coordinates

---

## Code Processes That Write to These Tables

### 1. Creating/Updating Spaces

**Controller**: [SpaceController.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Http/Controllers/SpaceController.php)

**Store Method** (L166-187):
```php
public function store(SpaceStoreRequest $request)
{
    $space = new Space();
    $inputs = $request->validated();
    $space->saveFromInputs($inputs);  // Saves space data including city
    
    // Attach media if provided
    if ($request->has('logo')) {
        $this->spaceService->replaceLogo($space, $request->logo);
    }
    if ($request->has('cover')) {
        $this->spaceService->replaceCover($space, $request->cover);
    }
    
    return redirect()->route('admin.spaces.edit', $space->id);
}
```

**Update Method** (L312-331):
- Similar flow: validates inputs, saves via `saveFromInputs()`, updates media

**saveFromInputs Method** in [Space.php:L220-244](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Entities/Space.php#L220-L244):
```php
public function saveFromInputs(array $inputs)
{
    $this->name = $inputs['name'];
    $this->latitude = $inputs['latitude'] ?? null;
    $this->longitude = $inputs['longitude'] ?? null;
    $this->address = $inputs['address'] ?? null;
    $this->type_id = $inputs['type_id'] ?? null;
    $this->contacts = $inputs['contacts'] ?? [];
    $this->city = Arr::get($inputs, 'city');              // ← City saved here
    $this->country_code = Arr::get($inputs, 'country_code');
    
    if (array_key_exists('parent_id', $inputs)) {
        $this->parent_id = $inputs['parent_id'];
    }
    
    if (array_key_exists('is_page_enabled', $inputs)) {
        $this->is_page_enabled = $inputs['is_page_enabled'] ?? false;
    }
    
    if (!empty($inputs['translations'])) {
        $this->fill($inputs['translations']);
    }
    
    $this->save();
}
```

### 2. Assigning Space Managers

**Controller**: [SpaceController.php:L352-363](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Http/Controllers/SpaceController.php#L352-L363)

```php
public function updateManagers(Request $request, Space $space)
{
    $this->authorize('manageManager', Space::class);
    $space->managers()->sync($request->managers);  // ← Updates space_user pivot table
    
    $this->generateFlashMessage('The :resource was updated!', [
        'resource' => __('Manager')
    ]);
    
    return back();
}
```

**Process**: Receives an array of user IDs and syncs them to the `space_user` pivot table.

### 3. Creating Space Events

While we didn't find a backend controller for creating events directly, the frontend shows events are created through the Space edit page (modal dialogs). The events likely use standard Eloquent `create()` or `save()` methods that populate the `space_events` table with data including the city field.

---

## Analysis for City Administrator Role

### Current State

> [!WARNING]
> **Data Inconsistency Risk**: Cities are currently stored as free-text strings in two separate tables (`spaces.city` and `space_events.city`). This creates potential issues:
> - Typos and variations ("Manila" vs "manila" vs "Manila City")
> - No referential integrity
> - Difficult to query all spaces/events for a specific city reliably
> - Cannot easily assign a user to manage "all spaces in Manila"

### Can Existing Tables Support City Administrator Role?

**Short Answer**: Partially, but not ideally.

**What Works**:
- ✅ The `space_user` pivot table mechanism can be reused for city-level permissions
- ✅ The hierarchical nested set structure allows spaces to be organized by city (if structured that way)
- ✅ Existing city data can be migrated to a new normalized structure

**What Doesn't Work**:
- ❌ **No city normalization**: Can't reliably grant "administrator for Manila" when "Manila" might be spelled differently
- ❌ **No city entity**: No dedicated table to represent cities as first-class entities
- ❌ **Authorization complexity**: Current authorization relies on direct `space_user` relationships, not city-based grouping

### Recommendations

> [!IMPORTANT]
> **Recommended Approach**: Create a new normalized city structure to support the City Administrator role properly.

#### Option 1: Add New `cities` Table (Recommended)

```sql
CREATE TABLE cities (
  id BIGINT PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  country_code VARCHAR(3),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE(name, country_code)
);

-- Pivot table for city administrators
CREATE TABLE city_user (
  id BIGINT PRIMARY KEY,
  city_id BIGINT FOREIGN KEY REFERENCES cities(id),
  user_id BIGINT FOREIGN KEY REFERENCES users(id)
);

-- Update spaces table
ALTER TABLE spaces 
  ADD COLUMN city_id BIGINT FOREIGN KEY REFERENCES cities(id),
  -- Keep old city column for migration, can deprecate later
  ADD INDEX idx_city_id (city_id);

-- Update space_events table
ALTER TABLE space_events 
  ADD COLUMN city_id BIGINT FOREIGN KEY REFERENCES cities(id),
  ADD INDEX idx_city_id (city_id);
```

**Benefits**:
- ✅ Clean separation of concerns
- ✅ Referential integrity
- ✅ Easy to query: "show all spaces for city ID X"
- ✅ Can migrate existing data by normalizing text values
- ✅ Similar pattern to existing `space_user` pivot

**Migration Strategy**:
1. Create `cities` table
2. Extract unique cities from `spaces.city` and `space_events.city`
3. Populate `cities` table
4. Add `city_id` foreign keys to `spaces` and `space_events`
5. Map existing text values to city IDs
6. Keep old `city` column as fallback during transition

**Recommended Library for City Data**:

To populate the `cities` table and provide a better UX for city selection, use the **`country-state-city`** npm package:

```bash
npm install country-state-city
```

**Features**:
- 250+ countries, 5,000+ states, 150,000+ cities
- Lightweight and actively maintained
- Simple API: `City.getCitiesOfCountry(countryCode)`
- Works on both frontend and backend (Node.js)

**Usage Example**:
```javascript
import { City } from 'country-state-city';

// Get all cities for Philippines
const cities = City.getCitiesOfCountry('PH');

// Example output:
// [{ name: 'Manila', countryCode: 'PH', stateCode: '00', latitude: '14.60', longitude: '120.98' }, ...]
```

**Implementation Steps**:
1. Install the library
2. Create admin API endpoint to fetch cities by country
3. Update frontend forms to use dropdown/autocomplete instead of free text input
4. Use the library's data to populate the `cities` table during migration
5. Add validation to ensure city names match library data

---


#### Option 2: Use Existing Nested Set Hierarchy

Set up spaces so that top-level parent spaces represent cities, and grant users management rights to the city-level parent space.

**Benefits**:
- ✅ No schema changes needed
- ✅ Uses existing `space_user` mechanism

**Drawbacks**:
- ❌ Requires strict organizational structure (city → venue → sub-venue)
- ❌ Inflexible: events must always belong to city-parent spaces
- ❌ Confuses "city" concept with "space" concept
- ❌ Still has free-text city fields that may not match parent space names

---

## Conclusion

### Summary

| Table | Purpose | City Data Storage | Suitable for City Admin? |
|-------|---------|-------------------|--------------------------|
| `spaces` | Physical venues | `city` (varchar, free text) | ⚠️ Needs normalization |
| `space_user` | User-Space managers | N/A | ✅ Pattern can be reused for `city_user` |
| `space_events` | Events at spaces | `city` (varchar, free text) | ⚠️ Needs normalization |

### Final Recommendation

**You will need to add new tables** (`cities` and `city_user`) to properly implement the City Administrator role. The existing `space_user` pattern provides a good blueprint, but the lack of city normalization makes it unreliable to map users to cities directly in the current schema.

The implementation should follow the [City Administrator Role Guide](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/docs/roles/CityAdministratorRole.md) and create:
1. A new `cities` table
2. A new `city_user` pivot table (mirroring `space_user`)
3. Foreign key relationships from `spaces` and `space_events` to `cities`
4. Authorization policies that check city-level permissions
5. A data migration to normalize existing free-text city values

---

## Related Files

- [SpaceController.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Http/Controllers/SpaceController.php) - Main backend controller
- [Space.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Entities/Space.php) - Space model
- [SpaceEvent.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Entities/SpaceEvent.php) - Event model
- [SpaceService.php](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Services/SpaceService.php) - Space business logic
- [Migration: spaces](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Database/Migrations/2022_07_07_115330_create_spaces_table.php)
- [Migration: space_user](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Database/Migrations/2022_07_14_090404_create_space_user_table.php)
- [Migration: space_events](file:///Users/ismaelwc/Development/optim8/optim8-buskincity/modules/Space/Database/Migrations/2022_07_28_120425_create_space_events_table.php)
