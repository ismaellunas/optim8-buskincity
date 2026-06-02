# Form Builder (Module) — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — detailed trace of the dynamic form engine, submission lifecycle, and automated user creation logic.

---

## 1. Form Submission Lifecycle

The Form Builder uses a polymorphic field system where each field type (Text, Select, File, etc.) has its own logic class for data handling.

### Flow: Client Submission (`POST /form-builders/save`)
1. **Middleware**: `recaptcha` (verified against Google), `throttle:defaultRequest`, and `verifyModule:FormBuilder`.
2. **Controller**: `Modules\FormBuilder\Http\Controllers\FormBuilderController@submit`
3. **Logic Decision (`saveValues` service)**:
    - **Schema Resolution**: Fetches the `Form` model and its `fieldGroups`.
    - **Field Proxying**: Iterates through inputs. For each field, it instantiates a specialized field class (e.g., `Modules\FormBuilder\Fields\TextField`) to sanitize the value via `getSavedData()`.
    - **Context Capture**: Uses `IPService` and `Jenssegers\Agent` to record the User's IP, Browser, Device, and Timezone.
4. **Persistence**:
    - **Table**: `form_builders` (Form definition).
    - **Table**: `form_builder_entries` (Submission header + context).
    - **Table**: `form_builder_entry_metas` (Individual field values stored via Key-Value pairs).
5. **Special Path (Hardcoded)**: if the `form_id` is `performer_application`, it dispatches a specialized `StreetPerformerApplicationReceived` email directly to the Super Admin.
6. **Event Dispatch**: `Modules\FormBuilder\Events\FormSubmitted`.

---

## 2. Notification Pipeline

### Flow: Email Dispatch (`SendFormNotification`)
1. **Trigger**: `FormSubmitted` listener.
2. **Execution**:
    - Fetches entries in `form_notification_settings`.
    - **Tag Replacement**: The `swapTagWithEntryValue` method uses `Str::swap` to replace placeholders (e.g., `{email}`, `{full_name}`) in the template with actual submitted data.
    - **Logic**: Validates "Rules" (e.g., "Only send if field X equals value Y") before dispatching.

---

## 3. Automated User Creation (`AutomateUserCreationService`)

One of the module's most advanced features is the ability to bridge form entries into the core `users` table.

- **Mapping**: Administrators define `mapping_rules` between Form Fields and User attributes.
- **Workflow**:
    - **Automatic**: Not found in default submission; typically requires a manual administrative review or a specific event trigger.
    - **Manual**: From `FormEntryController@show`, an admin can trigger `createOrUpdateUser`.
- **Validation**: Ensures that mandatory fields (Email, Name) are correctly mapped before a user record is generated.

---

## 4. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Validation** | `recaptcha` | External dependency on Google's API to prevent spam. |
| **Logic** | `Jenssegers/Agent` | PHP library used to parse the User-Agent string during submission. |
| **Persistence** | EAV Pattern | Uses `form_entry_metas` to avoid migrating the schema every time a form is updated. |
| **Shortcodes** | `Modules/FormBuilder/Shortcodes` | Allows embedding forms into Page Builder content via `[form id="..."]`. |

---

## 5. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `form_builders` | Stores the form name and its unique `key`. |
| `form_field_groups` | Groups fields together (Visual structure). |
| `form_fields` | Definitions of individual inputs (Label, Name, Type, Rules). |
| `form_entries` | Submission header (IP, Browser, User ID if logged in). |
| `form_entry_metas` | The actual submitted data (Key-Value pairs). |
| `form_notification_settings` | Templates and rules for email alerts. |

---

## 6. Migration Critical Notes

1. **Meta Lookup Overheads**: Because data is stored in `form_entry_metas`, a standard `SELECT * FROM entries` will return 0 user data. You MUST join on the metas table to reconstruct a submission.
2. **Hardcoded Form IDs**: The `performer_application` logic in `FormBuilderController@submit` is hardcoded. If this form's ID is changed in the UI, the automated Admin email will stop firing.
3. **Automate User Creation Rules**: These rules are stored as JSON in the form settings. Ensure the `user_id` mapped in these rules remains valid after migrating the `roles` table.
4. **Recaptcha Configuration**: If moving to a new domain, new Recaptcha keys must be generated and updated in the `settings` table, or all form submissions will fail with a 422 error.

---

*End of Form Builder Module Execution Flow*
