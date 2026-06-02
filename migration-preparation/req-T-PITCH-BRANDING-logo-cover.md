# Requirement Spec — Pitch Branding: Logo + Cover Image (T-PITCH-BRANDING)

> Source: OQ11 (answered 2026-06-02 — "create the needed requirement to implement this new feature").
> Status: **FINAL — sub-questions resolved 2026-06-02** · Owner: TBD · Target phase: **Phase 6 (Pitch UX)**
>
> **Resolved decisions:** SQ-A = logo/cover do **NOT** count toward the media cap · SQ-B = **BOTH REQUIRED** (a pitch cannot be published without a logo AND a cover) · SQ-C = **HARD-ENFORCE** dimensions/aspect (reject non-conforming uploads) · SQ-D = on removal, **DELETE** the underlying Media asset (with a guard: only delete if not referenced elsewhere; otherwise detach).
> Related: `00-START-HERE-implementation-guide.md` → T-PITCH-UX

---

## 1. Background / Problem
Pitches (the `Modules\Ecommerce\Entities\Product` "Event" type, edited via the Booking module) today only have a **single flat gallery** of media. The public cover image is derived implicitly:

```186:193:modules/Ecommerce/Entities/Product.php
    public function getCoverThumbnailUrl(): ?string
    {
        if ($this->gallery->isNotEmpty()) {
            return $this->gallery->first()->thumbnailUrl;
        }

        return null;
    }
```

There is **no dedicated logo** and **no deliberately chosen cover** — "cover" is just whichever gallery image happens to be first. The client wants pitches to carry their own **logo** (brand mark) and a **cover image** (hero/banner), distinct from the gallery.

## 2. Goal
Allow a pitch to have:
- **1 Logo** — square brand mark, shown in listings/nav/cards.
- **1 Cover** — wide hero image, shown at the top of the pitch page.
- The existing **Gallery** — unchanged (0..N additional images).

…manageable from the pitch create/edit form and rendered on the public pitch/landing pages.

## 3. Scope
**In scope**
- Data model to tag a pitch's logo and cover (reuse existing `mediables.type`).
- Pitch create/edit form: two new single-image pickers (Logo, Cover) above the Gallery.
- Validation (type, size, dimensions/aspect) for each.
- Public rendering: logo in cards/landing, cover on pitch page.
- Backward compatibility + a sensible fallback when logo/cover are unset.

**Out of scope (this requirement)**
- Cropping/editing UI beyond the existing media picker.
- Per-locale (translated) logos/covers.
- City/Country page branding (separate concern; see OQ9/Phase 7).
- Video logos/covers (images only).

## 4. Data Model — reuse `mediables.type` (NO new table)
The pivot already supports a discriminator, so **no migration of new tables is required**:

```16:25:database/migrations/2023_02_21_065903_create_mediables_table.php
        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->foreignId('media_id')
                ...
            $table->string('type', 64)->nullable();
            ...
```

Define pivot `type` values:
| `mediables.type` | Meaning | Cardinality per pitch |
|---|---|---|
| `null` or `gallery` | Existing gallery image | 0..N |
| `logo` | Brand logo | 0..1 |
| `cover` | Hero/cover image | 0..1 |

**Model changes (`Modules\Ecommerce\Entities\Product`)**
- `gallery()` → add `->withPivot('type')`.
- Add scoped relations/accessors:
  - `logo()` → `morphToMany(Media)` `wherePivot('type','logo')`.
  - `cover()` → `morphToMany(Media)` `wherePivot('type','cover')`.
  - `galleryItems()` → gallery where pivot type is null/`gallery` (so logo/cover don't appear in the gallery grid).
- Add `setLogo(?int $mediaId)`, `setCover(?int $mediaId)` — attach with the right pivot `type` (replace-on-set). On replace/removal (SQ-D = **delete**): detach the old pivot row, then **delete the underlying `Media` asset IF it is not referenced by any other `mediable`** (guard with `Media::getIsInUseAttribute`); if still in use elsewhere, detach only. Idempotent.
- Update `getCoverThumbnailUrl()`: prefer the dedicated `cover`, then `logo`, then first gallery item (keeps existing behavior as fallback → **no visual regression** for pitches without a cover).

> Note: existing `syncMedia()` syncs the whole `mediable` morph; it must be scoped to NOT wipe logo/cover. Implement gallery sync as `gallery type only` so saving the gallery never detaches logo/cover (and vice-versa).

## 5. API / Controller (Booking `ProductController`)
Current save reads a flat `gallery` array and calls `syncMedia`:

```211:214:modules/Booking/Http/Controllers/ProductController.php
        $mediaIds = $inputs['gallery'] ?? [];

            $product->syncMedia($mediaIds);
```
(mirrored in `update()` ~line 323.)

**Changes** in `store()` and `update()`:
- Accept two new optional inputs: `logo` (single media id, nullable) and `cover` (single media id, nullable).
- Persist via `$product->setLogo($inputs['logo'] ?? null)` and `$product->setCover($inputs['cover'] ?? null)`.
- Keep gallery sync scoped to gallery-type only.
- Extend the page props `dimensions` (line ~126/291) to include `logo` and `cover` recommended dimensions.

**Validation (`ProductRequest`)**
- `logo` / `cover`: integer, `exists:media,id`, must be an image (`extension ∈ constants.extensions.image`).
- **Required when publishing (SQ-B):** when `status = published`, BOTH `logo` and `cover` are **required**. When `status = draft`, both may be empty (so a pitch can be saved as a draft while branding is gathered). Use conditional rules (`required_if:status,published`).
- **Hard-enforced dimensions/aspect (SQ-C):** reject uploads that don't meet the constraints (validation error, not just a hint). Read the stored `media.assets` width/height to validate.
  - Logo: **square** (1:1, tolerance ±2%), min **256×256**, recommended **512×512**.
  - Cover: **8:3** ratio (±5% tolerance), min **1200×450**, recommended **1600×600**.
- Add `constants.dimensions.logo`, `constants.dimensions.cover` and `constants.recomended_dimensions.{logo,cover}` plus the min/ratio constraints to mirror the existing `gallery` pattern.

## 6. UX (Pitch form — `ProductForm.vue` / Create+Edit)
- Add a **Logo** single-image picker and a **Cover** single-image picker, placed **above** the existing Gallery section. Both marked **required to publish** (SQ-B) — surface a clear inline hint and block the Publish action until both are present.
- Each shows: current image thumbnail, "Replace" and "Remove" actions, and the **required** dimension constraints (reuse the existing media-picker component in single-select mode).
- Logo preview rendered square; cover preview rendered wide.
- Client-side, mirror the **hard** dimension/aspect checks (SQ-C) before upload for fast feedback; server remains the source of truth.
- The Gallery picker continues to exclude logo/cover. Per SQ-A, **logo/cover do NOT count** toward `maxProductFileNumber`.

## 7. Public rendering
- **Landing / pitch cards** (`FrontendProductShow.vue`, EventsCalendar, listing cards): use `logo` when present, else fall back to current cover-thumbnail logic.
- **Pitch detail page**: render `cover` as the hero/banner; render `logo` as the page's brand mark. If `cover` absent → fall back to first gallery image (current behavior) → then placeholder.

## 8. Backward compatibility & migration
- No destructive change. Existing pitches keep their gallery; logo/cover simply start empty.
- `getCoverThumbnailUrl()` fallback chain guarantees existing pitches still show an image.
- **No backfill required.** (Optional, deferred: a one-off script could promote each pitch's first gallery image to `cover` — NOT in scope unless requested.)
- **Grandfathering (SQ-B):** the "logo+cover required to publish" rule applies on the **next publish/save transition**. Already-published pitches are **not** retroactively unpublished; they should be flagged in the admin list as "missing branding" so owners can complete them.

## 9. Acceptance Criteria
1. Existing **draft** pitches with no logo/cover still load/save; cover-thumbnail fallback = first gallery image (no regression).
2. An admin can upload/select a logo and a cover on create and on edit, and replace/remove either independently.
3. Saving the gallery does not detach logo/cover, and saving logo/cover does not alter the gallery.
4. A pitch has at most one logo and one cover (re-selecting replaces).
5. **A pitch cannot be set to `published` without BOTH a logo and a cover** (SQ-B); attempting to do so returns field-level validation errors.
6. **Uploads that violate the dimension/aspect rules are rejected** (SQ-C) — logo must be ~square ≥256², cover ~8:3 ≥1200×450.
7. Public pitch page shows the chosen cover as hero and logo as brand mark; cards prefer the logo.
8. Invalid inputs (non-image, missing media id) are rejected with field-level messages.
9. Logo/cover do **not** count toward `maxProductFileNumber` (SQ-A).
10. Removing a logo/cover **deletes** the underlying Media asset when it's not used elsewhere; if shared, it is only detached (SQ-D).

## 10. Test cases (T-TESTS)
- Unit: `setLogo/setCover` attach with correct pivot type; setting null detaches; replace keeps single.
- Unit: `galleryItems()` excludes logo/cover; `gallery` sync preserves logo/cover.
- Feature: store/update with logo+cover persists; update without them leaves them untouched.
- Feature: validation rejects non-image / non-existent media for logo & cover.
- Regression: pitch with only gallery still returns a cover thumbnail.

## 11. Sub-questions — RESOLVED (2026-06-02)
- **SQ-A:** Count toward `maxProductFileNumber`? → **NO.**
- **SQ-B:** Required or optional? → **REQUIRED to publish** (both logo + cover); draft may be empty.
- **SQ-C:** Aspect/dimension enforcement? → **HARD-ENFORCE** (reject non-conforming uploads).
- **SQ-D:** Removal behavior? → **DELETE** the underlying Media asset (guarded: only if not referenced elsewhere; otherwise detach).

## 12. Test cases — addendum for resolved decisions
- Feature: setting `status=published` without logo and/or cover → validation error; with both → succeeds.
- Feature: uploading a non-square logo or non-8:3 cover (or below min size) → rejected.
- Unit: removing a logo whose Media is used by no other mediable → Media row deleted; if shared → only detached.
- Feature: draft pitch saves fine with no logo/cover.
