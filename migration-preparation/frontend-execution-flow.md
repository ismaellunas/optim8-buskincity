# Frontend & Public Pages — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of how public-facing pages are resolved, localized, and rendered from DB-driven dynamic content.

---

## 1. Localization & Language Resolution (The First Layer)

Every public request passes through a complex localization stack before hitting a controller.

### Chain: `LocalizationRedirect` → `RedirectOriginLanguage` → `HandleInertiaRequests` (skipped)

1. **`LocalizationRedirect`** (mcamara):
    - Re-routes URLs missing a language prefix (e.g., `/about` → `/en/about`).
2. **`RedirectOriginLanguage`** (`App\Http\Middleware\RedirectOriginLanguage`):
    - **Logic**: Reads the `buskincity_origin_language` cookie (via `LanguageService`).
    - **Action**: If a user has a "remembered" language that differs from the current URL prefix, it redirects them to their preferred language version.
    - **Hidden Dependency**: Skips redirection if `LoginService::hasHomeUrl()` is present (prevents breaking admin/performer portal sessions).
3. **`AdjustOriginLanguage`** (`App\Http\Middleware\AdjustOriginLanguage`):
    - Runs on dynamic routes.
    - **Action**: If the user land on a specific locale (e.g., `/fr/home`), it updates their `buskincity_origin_language` cookie to match.

---

## 2. Dynamic Page Resolution (`PageController`)

This controller handles the homepage and all custom pages created via the CMS.

### Flow A: Homepage (`GET /`)
1. **Controller**: `PageController@homePage`
2. **Logic**:
    - Calls `SettingService::getHomePage()` to retrieve the `id` from the `settings` table.
    - If no ID is set, falls back to a static `view('home')`.
    - If ID exists, fetches the `Page` model with published `translations`.
3. **Multi-language logic**:
    - If the home page lacks a translation for the current locale → calls `goToPageWithDefaultLocaleOrFallback()`.
    - Attempts to show the translation in the **Default Locale**.
    - If **that** also fails → returns the static default home view.

### Flow B: Dynamic Pages (`GET /{slug}`)
1. **Route Binding**: Laravel automatically resolves `PageTranslation` by its `slug`.
2. **Logic**:
    - Checks `isPublished` status.
    - **Permission Bypass**: If not published, only users with `page.read` permission (admins) can view it (preview mode).
    - **Slug Canonicalization**: If the user visits a slug that belongs to the page but not for the current locale, it redirects to the localized slug.
    - **Example**: Visiting `/fr/about-us` when the French slug is `/fr/a-propos` triggers a redirect.

---

## 3. Page Builder Rendering (The Data Layer)

The system uses a custom JSON-based Page Builder.

### Component Resolution
- **Model**: `PageTranslation` has a `data` column (JSON).
- **Structure**:
    - `entities`: The raw component data (text, images, settings).
    - `structures`: The layout (rows, columns) that references entity IDs.
- **Service**: `PageService::getEntityClassName($componentName)`
    - Searches `App\Entities\PageBuilderComponents\{Name}`.
    - If not found, scans enabled modules: `Modules\{Module}\Entities\PageBuilderComponents\{Name}`.

### Media Handling in Pages
- **Controller**: `getPageImages()` collects all media IDs from the Page Builder JSON.
- **Database**:
    - `SELECT * FROM media WHERE id IN (...)`
    - Eager loads `media_translations` to get `alt` tags.
- **Side Effect**: Media URLs are generated via `CloudinaryStorage` or local disk based on current environment settings.

---

## 4. Blog & Content Flow (`PostController`)

### Flow: Blog Index (`GET /blog`)
1. **Query**:
    - `Post::published()` (where status is 'published').
    - `orderBy('published_at', 'DESC')`.
2. **Localization**: Only returns posts that have a translation in the current locale.
3. **Response**: Classic Blade view (`blog.index`).

### Flow: Post Show (`GET /blog/{slug}`)
1. **Resolution**: `PostTranslation` by `slug`.
2. **View**: Uses `PostService` to prepare data, including related posts and formatted content.

---

## 5. Side Effects & External Dependencies

| Feature | Dependency | Side Effect |
|---------|------------|-------------|
| **SEO** | `meta_title`, `meta_description` columns | Injected into `<head>` via Blade layouts. |
| **Sitemap** | `SitemapController` / `SitemapService` | Dynamically generates `sitemap_index.xml` by scanning all published pages/posts. |
| **Branding** | `SettingService` | Fetches Site Logo, Favicon, and Custom CSS fields from `settings` table on every request. |
| **Geo-Location** | `RedirectOriginLanguage` | Relies on `LanguageService` which might trigger an IP lookup if no cookie exists. |
| **Styles** | `StylePageBuilderController` | Generates a dynamic CSS file based on Page Builder `uid`. |

---

## 6. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `pages` | Metadata for pages (home page flag, author). |
| `page_translations` | Content, Slugs, and `data` (Page Builder JSON). |
| `posts` | Blog post parent records. |
| `post_translations` | Blog content and slugs per language. |
| `settings` | Stores `home_page_id` and theme configuration. |
| `media` | Referenced by Page Builder entities. |

---

## 7. Migration Critical Notes

1. **Slug Collisions**: Slugs are unique across `page_translations`. The `PageService::getUniqueSlug()` method handles collisions by appending timestamps.
2. **Module Coupling**: Page Builder components are often physically located inside Module folders. If a module is disabled, its components will fail to render (sanitized via `PageService::sanitizeTranslationFromDisabledComponents`).
3. **Asset URLs**: All public images rely on the `media` table. Moving to a new environment requires ensuring `CLOUDINARY_URL` or local storage paths are correctly mapped, or the Page Builder layouts will break.

---

*End of Frontend & Public Pages Execution Flow*
