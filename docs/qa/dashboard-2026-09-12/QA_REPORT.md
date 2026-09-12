# Dashboard QA and UX report

> **Remediation update — 12 September 2026:** All 14 confirmed findings are fixed. Retesting passed 61 browser/UX scenarios and 24 automated tests (151 assertions). See the [retest report](retest/RETEST_REPORT.md) for closure details and evidence. The original assessment below is retained as the before-fix record.

**Project:** Pashupati Views Hotel  
**Date:** 12 September 2026  
**Reviewed revision:** `6ff0fe36de78917f413c57ba40e5be01ac572982`  
**Original assessment:** Needs fixes before routine mobile administration and dependable room creation.

## Results at a glance

| Test layer | Result |
|---|---|
| Existing automated suite | **18 passed**, 102 assertions |
| Additional functional, browser, and UX scenarios | **59 executed: 38 passed, 21 failed** |
| Consolidated findings | **14 open: 4 High, 8 Medium, 2 Low** |
| Frontend production build | Passed |
| Uncaught page JavaScript exceptions in main browser run | None observed |

The failed scenarios are grouped by underlying problem: for example, clipped actions on four mobile screens and one tablet screen are one finding. Counts describe this test set, not a percentage of all possible product behavior.

The main risks are reproducible server errors during room creation, missing validation on room gallery uploads, and administrative actions that cannot be reached on smaller screens. Login, role checks, major content-management flows, blog publishing, enquiry status changes, and several upload checks passed with synthetic data.

**During the original QA-only task, no production code, hotel records, or live uploads were changed.** The findings below were open at that point. The subsequent remediation and verified closure are documented in the retest report linked above.

## Test environment and method

- Chrome **144.0.7559.109**, headless Playwright, Linux.
- Viewports: desktop **1440 × 1000**, mobile **390 × 844**, and targeted tablet checks at **768 × 1024** and **1024 × 1024**.
- Separate local instance at `http://127.0.0.1:9011` with application source mounted read-only.
- Dedicated SQLite database, sessions, logs, view cache, and uploaded files in an ignored QA directory, mounted as `/qa`.
- Synthetic active admin, inactive admin, non-admin, hotel content, and enquiries. No real user password was used.
- Actual browser form submissions and interactions for login, room creation, image previews, settings validation, keyboard navigation, and mobile saving. Authenticated HTTP requests with CSRF tokens covered additional CRUD and boundary cases.
- UX assessment combined task walkthroughs, screenshots, keyboard tests, DOM checks, and a targeted contrast measurement. **No recruited-user study or formal accessibility certification was performed.**

The QA bootstrap refuses an unexpected database connection. The existing suite also refuses a database other than in-memory SQLite. The test server uses no hotel MySQL connection.

## Workflow coverage

| Area | Verified behavior | Important gaps found |
|---|---|---|
| Login and access | Wrong password feedback; active-admin login; inactive/non-admin rejection; guest redirect; logout; CSRF rejection | Live/restored account credentials were not checked |
| Dashboard and navigation | All 11 admin landing pages returned HTTP 200 | Mobile keyboard behavior; active sidebar state |
| Rooms | Create with amenities; image preview and individual removal; edit; visibility toggle; invalid/oversize cover rejection | Blank amenities crash; duplicate name crash; gallery accepts non-images; mobile clipping |
| Packages | Create/edit; price displayed through existing unit tests | Decimal input blocked; inconsistent guest limits accepted |
| Experiences, services, testimonials | Create, edit, toggle, delete; invalid testimonial rating rejected | Experiences/services mobile actions clipped |
| Carousel | Image create/replace/hide/delete; existing media tests passed | No exhaustive codec/browser matrix |
| Gallery | Image/video upload; invalid general-gallery media rejected; video toggle/delete | Uploaded captions/section have no edit control |
| Blogs | Draft private; publish public; edit/delete; image preview/clear; existing upload cleanup tests | Mobile success feedback hidden |
| Enquiries | Filter preserved through pagination; open marks read; status update persists | Mobile View/Delete actions clipped |
| Settings | Location saves and feeds public map | Validation loses unsaved textarea content; unassociated labels |

## High-priority findings

### QA-01 — Blank optional Amenities causes room creation to fail

**Severity:** High · **Evidence:** `ROOM-00` · [Screenshot](screenshots/ROOM-00.png)

**Reproduce:** Open Rooms → Add Room. Enter a new name and a valid price. Keep the default category/guest count, leave Amenities blank, and click Create Room.

**Actual:** HTTP 500. The server reports that `RoomController::parseAmenities()` received `null` instead of a string.  
**Expected:** An optional blank Amenities field saves as an empty list.  
**Impact:** A normal room-creation workflow fails even when its required fields are valid.

**Cause/fix:** Laravel converts empty strings to `null`; the input default only handles a missing key. Normalize `null` before calling the parser or accept nullable input and return an empty list. Audit update as well; it calls the same parser.

**Source:** `app/Http/Controllers/Admin/RoomController.php:33,57,105`.

### QA-02 — Duplicate room names cause an unhandled database error

**Severity:** High · **Evidence:** `ROOM-03` · [Screenshot](screenshots/ROOM-03.png)

**Reproduce:** Create `QA Garden Room` with valid values and nonblank amenities. Create another room with the same name.

**Actual:** HTTP 500 with a unique constraint failure on `rooms.slug`.  
**Expected:** Clear validation explaining the duplicate, or generation of a unique slug.  
**Impact:** An ordinary naming collision interrupts content entry and provides no actionable form error.

**Cause/fix:** Creation derives the slug from the name without checking uniqueness. Validate the derived slug or generate collision-safe slugs, including different names that normalize to the same slug.

**Source:** `app/Http/Controllers/Admin/RoomController.php:34`; unique slug in `database/migrations/2026_09_10_000001_create_cms_tables.php`.

### QA-03 — Room gallery accepts files that are not images

**Severity:** High · **Evidence:** `ROOM-04`

**Reproduce:** Submit a valid room with a harmless text file as `gallery_images[0]`, bypassing the browser file-picker filter.

**Actual:** The text file is accepted and a room/gallery entry is created.  
**Expected:** Server-side validation rejects non-images and enforces per-file limits.  
**Impact:** Invalid files can enter public media storage and create broken room galleries. The UI's `accept` attribute does not enforce server rules.

**Cause/fix:** `gallery_images` and its items are absent from room validation. Validate an optional array plus every file's image type and size before creating/updating records or storing any files. The standalone Gallery and room cover validators already reject tested invalid files.

**Source:** `app/Http/Controllers/Admin/RoomController.php:84–100,110–119`. Testing used benign text only; executable payloads were not tested.

### QA-04 — Table actions are clipped on mobile and at the tablet breakpoint

**Severity:** High · **Evidence:** `MOBILE-rooms`, `MOBILE-experiences`, `MOBILE-services`, `MOBILE-enquiries`, `TABLET-768`

**Reproduce:** At 390px width, open Rooms, Experiences, Services, or Enquiries with records present. Try to reach actions in the rightmost columns. Repeat Rooms at 768px width.

**Actual:** The tables extend beyond containers with `overflow-hidden`, clipping status, Edit/Delete, or View/Delete controls. At 768px, the room table measured approximately **674px inside a 464px container**.  
**Expected:** Actions stay reachable through responsive cards, prioritized columns, or a clearly scrollable table region.  
**Impact:** Users cannot complete core administration tasks at common mobile/tablet sizes. The targeted room test passed at 1024px.

**Recommended fix:** Use accessible horizontal scrolling or mobile record cards, and account for the permanent sidebar width beginning at 768px. Keep primary actions visible.

**Sources:** `resources/views/admin/{rooms,experiences,services,enquiries}/index.blade.php` table wrappers.  
**Screenshots:** [Mobile rooms](screenshots/mobile-rooms.png), [mobile enquiries](screenshots/mobile-enquiries.png), [768px rooms](screenshots/tablet-rooms-768.png).

## Medium-priority findings

### QA-05 — Shared fields drop decimal-step and required attributes

**Evidence:** `FORM-01`, `UX-02`

**Reproduce:** In Add Package, enter `100.50` in Price From. Inspect or submit the field. In Add Room, inspect the required Room Name field.

**Actual:** Price From has no `step` attribute and reports `stepMismatch`, so normal browser submission rejects decimal amounts. Room Name has a required star but no native `required` attribute.  
**Expected:** Package prices accept two decimal places; required fields expose their requirement to browsers and assistive technology.

**Recommended fix:** Forward the Blade component's attribute bag to the actual input and bind `required`. Package form already supplies `step="0.01"`, but the shared component discards it. Server-side required validation still exists.

**Sources:** `resources/views/components/admin/field.blade.php`; `resources/views/admin/packages/form.blade.php:57`.

### QA-06 — Packages allow contradictory guest limits

**Evidence:** `PACKAGE-02`

**Reproduce:** Save a package with Min Guests `5` and Max Guests `2`, then reopen it.

**Actual:** Both values persist.  
**Expected:** Reject a maximum below the minimum and explain the relationship.  
**Recommended fix:** Add a cross-field validation rule while allowing the documented blank/unlimited maximum.

**Source:** `app/Http/Controllers/Admin/PackageController.php:92–93`.

### QA-07 — Gallery items cannot be edited through the dashboard

**Evidence:** `GALLERY-02`

**Reproduce:** Upload a photo, then attempt to change its caption, badge, or section from Gallery.

**Actual:** The item exposes only visibility and delete controls. No edit control/form is present.  
**Expected:** Existing media metadata can be corrected without deleting and uploading the file again.  
**Recommended fix:** Add an edit dialog or inline form using the existing update route.

**Sources:** `resources/views/admin/gallery/index.blade.php`; `GalleryController::update()`.

### QA-08 — A settings validation error discards unsaved textarea edits

**Evidence:** `SETTINGS-02` · [Screenshot](screenshots/SETTINGS-02.png)

**Reproduce:** Change Hero Body Text without saving. Switch to Contact, enter more than 500 characters in Map Location, and save all settings. After the error, return to Hero.

**Actual:** Hero Body Text reverts to its previously saved value.  
**Expected:** Retain every submitted field while the user corrects the error.  
**Impact:** Editing several tabs can lead to lost work.

**Recommended fix:** Render textarea, rich-text, and checkbox values from submitted old input, as text inputs already do. Restore the relevant tab or focus the invalid field.

**Source:** `resources/views/admin/settings/index.blade.php:55–66`.

### QA-09 — Mobile sidebar lacks keyboard and expanded-state handling

**Evidence:** `UX-04`, `UX-05`, `UX-09`

**Reproduce:** At 390px, press Tab with the sidebar closed. Open it and press Escape. Inspect the menu button's expanded state.

**Actual:** Focus enters the offscreen sidebar; Escape does not close it; the button has no `aria-expanded` value.  
**Expected:** Closed navigation should not receive focus. Opening/closing should manage focus and expose state, with Escape available to dismiss it.

**Recommended fix:** Use hidden/inert state when closed, bind expanded state, support Escape, return focus to the trigger, and constrain focus appropriately while the mobile overlay is open.

**Source:** `resources/views/layouts/admin.blade.php:20–24,107–109,159–175`.

### QA-10 — Room image pickers cannot be activated using only the keyboard

**Evidence:** `UX-03`

**Reproduce:** Open Add Room and Tab through the form to Cover Image/Gallery Images.

**Actual:** File inputs use `display:none`, while their visible labels are not keyboard-focusable controls.  
**Expected:** Both upload actions can be reached and activated without a pointer.  
**Recommended fix:** Keep accessible file inputs in the focus order with visually hidden styling, or provide real buttons that activate the inputs and show a visible focus state.

**Source:** `resources/views/admin/rooms/form.blade.php`, cover/gallery upload controls.

### QA-11 — Settings controls lack associated labels

**Evidence:** `UX-07`, main-page DOM inventory

**Reproduce:** Open Site Settings and inspect a text/textarea control's associated label. The tested fixture contained **29 controls without an associated label**. The Gallery Section select also lacked one.

**Actual:** Labels are visually adjacent but do not point to controls with `for`/`id`; the controls have no equivalent accessible naming.  
**Expected:** Each form control has a programmatic label.  
**Recommended fix:** Generate stable IDs and matching `for` values; preserve equivalent labels for enhanced editors.

**Sources:** `resources/views/admin/settings/index.blade.php:50–72`; Gallery Section select.

### QA-12 — Successful saves have no visible confirmation on mobile

**Evidence:** `UX-06` · [Screenshot](screenshots/mobile-save-feedback.png)

**Reproduce:** At 390px, create a blog draft successfully.

**Actual:** The saved record appears, but the success message is hidden. Its only container uses `hidden sm:inline-flex`.  
**Expected:** A visible, accessible success message at every viewport size.  
**Impact:** Users may be uncertain whether a save completed and repeat the action.

**Recommended fix:** Show a persistent inline success alert or accessible status notification in the content area, including on mobile.

**Source:** `resources/views/layouts/admin.blade.php:123–127`.

## Low-priority findings

### QA-13 — Sidebar loses the active section during room editing

**Evidence:** `UX-01`

**Reproduce:** Open Rooms → Edit.  
**Actual:** Rooms is no longer highlighted, because matching uses the index route only.  
**Expected:** Highlight the owning section throughout create/edit/detail workflows.  
**Recommended fix:** Match route families consistently, as Blogs already does.

**Source:** Sidebar item route matching in `resources/views/layouts/admin.blade.php`.

### QA-14 — Upload helper text is difficult to read

**Evidence:** `UX-08` · [Screenshot](screenshots/UX-08.png)

The room-cover hint `JPG, PNG, WEBP (max 4MB)` is 12px and measured approximately **2.44:1 contrast** against its composed background. The measurement converts the browser's OKLCH colors to sRGB and accounts for ancestor alpha backgrounds. It is a targeted measurement, not a complete accessibility audit.

**Recommended fix:** Use a lighter text color and check the other muted hints and metadata. The test used 4.5:1 as its normal-text target.

**Source:** `text-gray-600` upload hint in `resources/views/admin/rooms/form.blade.php`.

## UX recommendations beyond confirmed defects

These are improvements to consider, not failed product requirements:

- Add an unsaved-changes warning to long editing forms and the multi-tab settings screen.
- Consider sticky save actions on long room forms, particularly on mobile.
- Make dashboard statistic cards useful shortcuts or reduce their button-like hover treatment.
- Add search/filter tools as room, blog, gallery, and enquiry counts grow.
- Give gallery icon-only status/delete actions clear accessible names and comfortable touch areas.

## Suggested fix and retest order

1. Normalize optional room input, handle duplicate slugs, and validate every room gallery file. Retest successful creation, validation errors, and file cleanup.
2. Make list actions reachable at 390px and 768px. Retest View/Edit/Status/Delete with populated tables.
3. Repair shared field attributes, package guest constraints, and settings input preservation.
4. Complete keyboard navigation, upload accessibility, form labels, and mobile feedback.
5. Add Gallery editing, then polish active navigation and muted text contrast.

Retest the failed scenarios after fixes and retain regression coverage for the two room server errors. Passing existing tests alone did not catch these failures.

## Evidence and limitations

- [Main browser results: 47 scenarios](browser-results.json)
- [Follow-up results: 12 scenarios](followup-results.json)
- [Existing test-suite summary](existing-tests.txt)
- [Build output](build-output.txt)
- [Reproduction setup and scripts](README.md)
- [Desktop dashboard screenshot](screenshots/desktop-dashboard.png)

The final ledger excludes an initial ambiguous pagination selector result; the corrected check passed. The contrast calculation was corrected for OKLCH before recording the final measurement. Room creation was rerun with explicit success verification after the first pass revealed the blank-amenities exception.

**Not covered:** live-account/data-recovery verification; production MySQL behavior beyond schema/code review; Firefox/Safari or physical touch devices; a screen-reader session; load/concurrency testing; every media codec and large-batch upload; full security or accessibility audits; delivery through external email/WhatsApp services. CDN/network behavior was not exhaustively tested. All page-render observations and synthetic data findings are specific to this reviewed revision.
