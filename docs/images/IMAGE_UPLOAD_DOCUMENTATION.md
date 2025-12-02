# Image Upload Documentation

## Overview

This document describes how image uploads are handled in the **Optim8 BuskinCity** application, from the front‑end component all the way to permanent storage.

---

### 1️⃣ Tech‑Stack
| Layer | Technology |
|------|------------|
| **Backend** | Laravel 9 (PHP 8.1+), PostgreSQL, Redis, **Cloudinary** (`cloudinary-labs/cloudinary-laravel`), Jetstream, Sanctum, Spatie Laravel‑Permission |
| **Frontend** | Vue 3 (Composition API) + Vite, Inertia.js, Bulma + Tailwind, **FilePond** (`vue-filepond` v7) |

---

### 2️⃣ Front‑end Upload Component
**File:** `resources/js/Biz/FileUpload.vue`
```vue
<template>
  <file-pond
    ref="pond"
    class-name="my-pond"
    :accepted-file-types="acceptedTypes"
    :allow-multiple="allowMultiple"
    :disabled="disabled"
    :label-idle="placeholder"
    :max-file-size="maxFileSize"
    :max-files="maxFiles"
    :max-total-file-size="maxTotalFileSize"
    @addfile="onAddFile"
    @updatefiles="onUpdateFiles"
    @removefile="onRemoveFile"
  />
</template>

<script>
import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';

const FilePond = vueFilePond(
  FilePondPluginFileValidateSize,
  FilePondPluginFileValidateType,
  FilePondPluginImagePreview
);
export default {
  name: 'FileUpload',
  components: { FilePond },
  /* props, emits, data, methods … */
};
</script>
```
*Key points*
- Accepts props for allowed MIME types, size limits, multiple files, etc.
- Emits `add-file`, `add-files`, `update-files` which parent pages (e.g., Media Create/Edit) forward as part of a `FormData` payload.

---

### 3️⃣ Backend API & Controllers
| File | Role |
|------|------|
| `app/Http/Controllers/MediaController.php` | Receives the multipart request (`store` / `apiStore`). Calls `syncMedia()` → `storeProcess()` → **`MediaService::upload()`**. |
| `app/Services/MediaService.php` | Core service that talks to the storage driver. Handles filename sanitisation, Cloudinary upload, thumbnail generation, and DB record creation. |
| `app/Entities/CloudinaryStorage.php` | Wrapper around the Cloudinary SDK (`cloudinary-labs/cloudinary-laravel`). |
| `app/Models/Media.php` | Eloquent model storing `cloudinary_public_id`, `file_url`, `thumbnail_url`, etc. |
| `routes/admin.php` | `Route::resource('/media', MediaController::class);` plus API endpoints (`/media/replace`, `/media/save-as-image`). |

**Upload flow (backend)**
1. `MediaController::store` → `syncMedia()` separates new files from updates.
2. For each new file, `MediaService::upload($file, $filename, new CloudinaryStorage())` is called.
3. `CloudinaryStorage` streams the file directly to Cloudinary.
4. A new `Media` record is persisted with the Cloudinary public ID and URLs.
5. The controller returns the created `Media` objects (JSON for API, redirect for normal flow).

---

### 4️⃣ Where the file is saved
| Layer | Location |
|------|----------|
| **Front‑end** | Temporary Blob in the browser (until the request is sent). |
| **Laravel request** | In PHP memory while the controller processes it. |
| **Permanent storage** | **Cloudinary CDN** – the file is uploaded via the Cloudinary SDK; no local disk copy is kept. |
| **Metadata** | `media` table in PostgreSQL – stores `cloudinary_public_id`, `file_url`, `thumbnail_url`, etc. |

The `.env` file (git‑ignored) holds the Cloudinary credentials:
```dotenv
CLOUDINARY_URL=cloudinary://<API_KEY>:<API_SECRET>@<CLOUD_NAME>
CLOUDINARY_UPLOAD_PRESET=your_preset
```
These values are read by `cloudinary-labs/cloudinary-laravel`.

---

### 5️⃣ Additional notes
- **Validation** – FilePond plugins enforce size and MIME‑type limits on the client; Laravel Form Requests (`MediaStoreRequest`) perform server‑side validation as a safety net.
- **Transformations** – After upload, `MediaService::transformMediaLibrary()` creates thumbnails and other derived assets on Cloudinary.
- **API endpoints** – `POST /admin/media` (create), `PUT /admin/media/{id}/update-image` (replace), `POST /admin/media/save-as-image/{id}` (duplicate), `POST /admin/media/replace` (API‑only replace).
- **Testing** – Use Laravel’s `UploadedFile::fake()->image('test.jpg')` and mock the Cloudinary driver for unit tests.

---

*Document generated on 2025‑12‑02.*
