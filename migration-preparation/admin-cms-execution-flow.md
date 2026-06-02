# Admin CMS (Pages, Posts, Categories, Media) — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of content management architecture, from the specialized CrudController foundations to polymorphic media and localized page building.

---

## 1. Architectural Foundation (`CrudController`)

Most CMS controllers (`Page`, `Post`, `Category`, `Media`) extend `App\Http\Controllers\CrudController`.

- **Standardization**: Provides `getData()`, `getCreateTitle()`, and `getIndexTitle()`.
- **Inertia integration**: Standardizes the props passed to Vue components, ensuring `baseRouteName` and `title` are consistent.
- **Side Effect**: Consistent breadcrumb and flash message behavior across all content modules.

---

## 2. Page & Page Builder Flow (`PageController`)

### Creation/Update Path
1. **Controller**: `PageController@store` / `update`
2. **Logic**:
    - **Page Model**: `saveFromInputs($inputs)` handles the main record and its translations.
    - **Author**: Automatically assigns `Auth::id()` to `author_id`.
    - **Media Integration**: `syncMediaFromInputs($inputs)` scans the Page Builder structure for media IDs to maintain polymorphic links.
3. **Data Model**: `PageTranslation`
    - Stores the Page Builder "Blueprint" in a JSON `data` column.
    - **Key Logic**: When saving, components are validated against enabled modules via `PageService::getEnabledModuleComponents()`.

### Duplication Logic
- **Action**: `PageController@duplicatePage($page)`
- **Execution**: Deep clones the `Page` model and all its `PageTranslation` records.
- **Side Effect**: Generates a new unique slug by appending a timestamp to avoid collisions.

---

## 3. Post & Blog Flow (`PostController`)

### Creation/Update Path
1. **Controller**: `PostController@store` / `update`
2. **Logic**:
    - **Category Sync**: `syncCategories($ids, $primaryId)` updates the `category_post` pivot table and designates one as `is_primary`.
    - **Cover Image**: Uses a dedicated `cover_image_id` column linked via `syncMedia()`.
    - **Scheduling**: Supports `scheduled_at`. If set, the post remains hidden from the frontend until a cron job updates status or the query filter passes (Laravel global scope behavior).
3. **Hidden Dependency**: `HasModuleViewData` trait. Some modules can inject extra data into the blog post create/edit views.

---

## 4. Category Flow (`CategoryController`)

### Localization Detail
- **Logic**: Like pages, categories are resolved by slug in the frontend but managed by ID in the admin.
- **Sync**: `saveFromInputs()` on the `Category` model handles the name translation per locale.

---

## 5. Media Management Flow (`MediaController`)

The project uses a highly integrated media library with **Cloudinary** as the primary engine.

### Upload Flow (`apiStore`)
1. **Service**: `MediaService@upload`
2. **Execution**:
    - **Sanitization**: Names are cleaned via `sanitizeFileName`.
    - **Cloudinary Call**: File is sent to Cloudinary API.
    - **DB Insert**: A record is created in `media` table with the Cloudinary `public_id` and `version`.
    - **Translations**: Media can have `alt` and `description` translated per locale in `media_translations`.

### Image Modification Flow
- **Update Image**: Replaces the source file on Cloudinary while KEEPING the same DB record (prevents broken links in Content).
- **Save as New**: Duplicates the record and provides a new version/file while replicating existing meta-data (translations).

### Polymorphic Linking (`Mediable`)
- **Relationship**: `morphMany` on `Page`, `Post`, and `User`.
- **Cleanup**: `MediaObserver` intercepts deletion and triggers a Cloudinary API call to remove the physical file.

---

## Hidden Dependencies & Cross-Cutting Concerns

| Action | Hidden Dependency | Side Effect |
|--------|-------------------|-------------|
| **Page Delete** | `MenuService` | Checks if page is in any menu. If so, warns or removes it. |
| **Media Delete** | `MediaObserver` | Calls Cloudinary API to delete remote asset. |
| **Post Save** | `PostObserver` | (Potentially) clears blog cache. |
| **Category Rename** | `CategoryObserver` | Updates slugs for all translations of that category. |
| **TinyMCE** | `tinymce.key` route | Fetches the Cloud-licensed key from `settings` table. |

---

## Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `pages` / `page_translations` | The backbone of CMS content; contains JSON builder data. |
| `posts` / `post_translations` | Blog content. |
| `categories` / `category_translations` | Blog taxonomy. |
| `category_post` | Pivot table for M:N blog-category relationship. |
| `media` / `media_translations` | Asset tracking and localized ALT tags. |
| `mediables` | Polymorphic table linking media to pages/posts/users. |

---

## Migration Critical Notes

1. **Page Builder JSON Paths**: The `data` column in `page_translations` uses entity IDs. During data migration, these IDs **MUST** remain consistent with the `media` table IDs or all images in the Page Builder will break.
2. **Cloudinary vs Local**: If migrating from Cloudinary to Local (or vice versa), the `StorageService` logic needs to be audited to ensure `file_url` accessors return correct absolute paths.
3. **Hardcoded Slugs**: Many menus link to slugs. If a migration changes the default locale, slugs must be carefully re-validated.

---

*End of Admin CMS Execution Flow*
