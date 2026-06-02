# Dashboard & Admin Widgets — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of the modular, extensible dashboard widget system used in the Admin portal.

---

## 1. Dashboard Entry (`DashboardController`)

The admin dashboard is a dynamic Inertia page composed at runtime.

### Flow: Loading the Dashboard (`GET /admin/dashboard`)
1. **Controller**: `DashboardController@index`
2. **Orchestration**: Calls `WidgetService` to collect three categories of widgets:
    - **Core Widgets**: Hardcoded system-wide widgets.
    - **Module Widgets**: Widgets dynamically registered by enabled modules.
    - **Stored Widgets**: Widgets persisted and ordered in the system configuration.
3. **Response**: Renders `DashboardAdmin` via Inertia, passing an array of widget definitions (including their UI component names and initial data).

---

## 2. Widget Discovery & Registration (`WidgetService`)

The `WidgetService` acts as a registry and factory for all widgets.

### Discovery Logic
- **Core Widgets**: Looks in `App\Entities\Widgets`. 
    - *Example*: `latestRegistration` → `App\Entities\Widgets\LatestRegistrationWidget`.
- **Module Widgets**: 
    1. Scans `ModuleService::getEnabledNames()`.
    2. Checks for a `widgets()` static method in the module's `ModuleService`.
    3. Resolves class in `Modules\{Module}\Widgets\{WidgetName}Widget`.
- **Stored Widgets**: 
    1. Fetches configuration from `SettingService::adminDashboardWidgets()`.
    2. Resolves based on the stored `widget` class string.

---

## 3. Widget Lifecycle & Security

All widgets must implement `App\Contracts\WidgetInterface`.

### Key Methods
- **`canBeAccessed()`**: Called by the service before including the widget in the response.
    - **Logic**: Usually checks Spatie permissions (e.g., `auth()->user()->can('user.browse')`).
- **`data()`**: Returns the UI configuration.
    - Includes: `title`, `componentName` (Vue component), `order`, and `i18n` translations.
- **`response()`** (Optional): If the widget is data-heavy, it provides an async response for AJAX loading.

---

## 4. Async Data Loading (`ApiWidgetController`)

To maintain dashboard performance, some widgets load their data asynchronously after the initial page burst.

### Flow: Fetching Widget Data (`GET /admin/api/widget/data/{uuid}`)
1. **Controller**: `ApiWidgetController@getStoredWidgetData`
2. **Logic**:
    - Uses the `UUID` to find the registered widget configuration in settings.
    - Instantiates the specific widget class.
    - Invokes `$widget->response()` to return JSON data.
3. **Example**: A "Recent Sales" widget might return a collection of `Order` models here.

---

## 5. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Widget Permissions** | `spatie/laravel-permission` | Implicitly controls which widgets appear on a user's dashboard. |
| **Module Widgets** | `ModuleService` | Widgets from disabled modules are automatically excluded from the discovery loop. |
| **Translations** | `WidgetInterface` | Each widget is responsible for its own `i18n` array, ensuring the dashboard is multilingual. |
| **Ordering** | `settings` table | Custom widgets are sorted based on an `order` key in the serialized settings array. |

---

## 6. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `settings` | Stores serialized widget configurations (UUIDs, column spans, custom properties, and display order). |
| `modules` | Determines which module-based widgets are active. |
| `users` / `roles` | Consulted by `canBeAccessed()` to determine visibility. |

---

## 7. Migration Critical Notes

1. **Serialized Class Names**: Stored widgets in the `settings` table often reference full PHP class namespaces (e.g. `\App\Entities\Widgets\X`). If a migration involves refactoring namespaces, these database values **must** be updated.
2. **Module UUIDs**: Async widgets rely on matching UUIDs between the frontend Vue component and the database record. Ensure these are preserved during data transfer.
3. **Permission Mapping**: If the new environment has different permission slugs, many widgets may "disappear" from the dashboard due to failed `canBeAccessed()` checks.

---

*End of Dashboard & Admin Widgets Execution Flow*
