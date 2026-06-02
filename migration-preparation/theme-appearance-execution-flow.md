# Theme & Appearance Settings — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of the theme engine, from settings persistence to dynamic CSS compilation and delivery.

---

## 1. Theme Configuration Storage

Theme settings are managed as individual key-value pairs in the `settings` table, grouped by functionality.

### Flow: Updating Theme Settings (e.g., Colors)
1. **Controller**: `ThemeColorController@update`
2. **Logic**:
    - Validates inputs (hex codes).
    - Iterates through inputs and updates `settings` records where `group = 'theme_color'`.
3. **Trigger**: Dispatches `CompileThemeCss` job to the queue.

---

## 2. The CSS Compilation Pipeline (`ThemeService`)

The system uses a "Settings-to-SASS" compilation pipeline to allow real-time theme changes without manual build steps.

### Path: `ThemeService@generateVariablesSass`
1. **Source**: Fetches current colors, fonts, and font-sizes from `SettingService`.
2. **Blade Rendering**: Renders internal Blade templates into SASS variable formats:
    - `theme_options.colors_sass` → `$primary-color: #hex;`
    - `theme_options.font_sass` → `$main-font: 'Roboto';`
3. **Persistence**: Writes these strings to temporary files in `storage/theme/sass/variables.sass`.

### Path: `ThemeService@generateCss`
1. **Execution**: Invokes Artisan command `webpack:theme-sass`.
2. **Action**: Compiles the main theme SASS entry point (which `@import`s the generated variables) using the system's build tools (Webpack/Vite wrapper) into a `.css` file.

### Path: `ThemeService@storeCssToSettingTable`
1. **Action**: Reads the final compiled CSS from `storage/theme/css`.
2. **Persistence**: Saves the **raw CSS text** back into the `settings` table (key: `css_app`).
3. **Versioning**: Updates the `version_css_app` setting with a `YmdHis` timestamp to act as a global cache buster.

---

## 3. Dynamic Asset Delivery (`StoredCssController`)

To avoid filesystem permission issues on distributed servers, the final CSS is served directly from the database.

### Flow: Delivery (`GET /css/stored/{css_name}`)
1. **Controller**: `StoredCssController` (Invokable).
2. **Logic**:
    - Calls `SettingService::storedCss($cssName)`.
    - Retrieves the CSS string from the `settings` table.
    - Resolves the `Last-Modified` header from the `version_css_app` timestamp.
3. **Response**: Returns a `text/css` response with aggressive caching headers (3 months in local, private in production).

---

## 4. Branding & Asset Linking

- **Logo/Favicon**: Managed via `ThemeAdvanceController`.
- **Database**: Stores the `media_id` in `settings` (group: `header`).
- **Resolution**: `SettingService::getLogoUrl()` resolves the ID via the `Media` model and generates an optimized Cloudinary/Local URL.
- **Cache**: Wrapped in `SettingCache` (Redis/File) to prevent hit-to-DB on every page load.

---

## 5. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **CSS Compiling** | `generate:theme-css` command | The actual shell command that bridges PHP and Node/SASS. |
| **Fonts** | Google Fonts API | `SettingService::getFontUrls()` builds a dynamic `<link>` tag based on stored font families. |
| **Purge** | `ThemeService::clearStorageTheme()` | Deletes the `storage/theme` directory after compilation to keep the disk clean. |
| **Inertia** | `HandleInertiaRequests` | Injects the current theme settings into the shared "global" state for Vue components. |

---

## 6. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `settings` | Primary store for hex codes, font names, Page ID of the homepage, and the **compiled CSS string**. |
| `media` | Stores physical assets like Logo and Favicon. |

---

## 7. Migration Critical Notes

1. **The "Stored CSS" Trap**: Because the compiled CSS is stored in the `settings` table, a raw DB migration will carry over old styles even if you change the SASS files on the new server. A manual `generate:theme-css` command must be run post-migration.
2. **Path Permissions**: The compilation process requires `storage_path('theme')` to be writable by both the web user and the CLI user (for the queue worker).
3. **Database Size**: Storing full CSS strings in a single DB row can be heavy. Ensure your `max_allowed_packet` (MySQL) or equivalent is sufficient if your theme grows complex.
4. **Cache Clearing**: `SettingCache` must be purged after migration, otherwise the site will attempt to load old Logo URLs or tracking codes.

---

*End of Theme & Appearance Settings Execution Flow*
