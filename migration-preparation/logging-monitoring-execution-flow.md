# Logging & Error Monitoring — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of the customized error reporting and system auditing architecture.

---

## 1. Automated Error Tracking (`ErrorLog`)

Unlike standard Laravel logs (which write to files), this system persists unique exceptions in the database for admin review.

### Flow: Exception Capture
1. **Trigger**: Any `Throwable` reaches `App\Exceptions\Handler`.
2. **Path**: `Handler@report` → `ErrorLogService@report`.
3. **De-duplication Logic**:
    - Calls `ErrorLog::syncErrorLog($inputs)`.
    - **Key Decision**: Matches existing records by `URL`, `File`, `Line`, `Message`, AND **Current Date**.
    - If a match exists for **today** → increments `total_hit` (prevents DB bloat during loops).
    - If no match → creates a new `ErrorLog` record with a JSON-casted `trace`.

---

## 2. System Auditing (`SystemLog`)

The platform leverages **Laravel Telescope** but wraps it in a custom Admin UI.

### Flow: Request & Event Tracking
1. **Background**: Telescope middleware records all entry points, DB queries, and events.
2. **Access**: `SystemLogController@index`.
3. **Logic**:
    - Resolves `tag` (e.g., `Auth:1`) from the request.
    - Aggregates Telescope entries for specific managers/super-admins.
4. **Side Effect**: In production, raw Telescope routes are hidden behind a redirect to this custom controller to ensure uniform UI and security.

---

## 3. Custom Exception Rendering (`Handler`)

The `ExceptionHandler` is heavily customized for Inertia and AJAX friendliness.

### Flow: Page Expiration (419)
- **Logic**: If a request fails due to `TokenMismatch` or `AuthenticationException` during an Inertia navigation:
    - Admin routes are redirected to `admin.login` via a custom `X-Inertia-Location` header.
    - Frontend routes are redirected to `login`.
    - Prevents "broken" modal states by forcing a full page reload to the login screen.

### Flow: Status-Specific Page Rendering
- **Trigger**: Status codes defined in `config('constants.theme_error_page')` (e.g., 403, 404, 500).
- **Resolver**: `responseErrorPage()`
    - Checks for `resources/views/errors/{code}.blade.php`.
    - Fallback: `resources/views/errors/error.blade.php`.
- **Logic Bypass**: If `APP_DEBUG=true` and state is 500, standard Laravel Ignition is shown instead of the themed error page.

---

## 4. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Telescope** | `laravel/telescope` | The underlying engine for Request/Auth logging. |
| **Purifier** | `Mews\Purifier` | Cleans error messages before display in the Admin tool. |
| **RBAC** | `error_log.delete` | Hard permission check in `ErrorLogController` to prevent non-admins from clearing audit trails. |
| **Pagination** | `ErrorLogService` | Custom pagination logic that filters by dynamic `dateRange` scopes. |

---

## 5. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `error_logs` | Centralized store for exceptions, stack traces, and hit counts. |
| `telescope_entries` | Raw request and event data (managed by Laravel Telescope). |
| `telescope_entries_tags` | Tag linking (e.g., Auth tags used for user searching in logs). |

---

## 6. Migration Critical Notes

1. **Table Maintenance**: `ErrorLog::truncate()` is available to the user. Ensure the `error_logs` table size is monitored, as stack traces can consume significant storage.
2. **Telescope Pruning**: Since System Logs rely on Telescope, ensure the `telescope:prune` command is scheduled in the new environment to keep the `telescope_*` tables from growing indefinitely.
3. **Production Redirects**: The production-only redirect in `web.php` for Telescope must be maintained to prevent exposing raw technical logs to non-admin users.
4. **Log Retention**: Moving the DB does NOT move the local `storage/logs/laravel.log`. For a full migration, both the DB `error_logs` and local file logs should be archived.

---

*End of Logging & Error Monitoring Execution Flow*
